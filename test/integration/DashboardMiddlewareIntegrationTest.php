<?php

declare(strict_types=1);

namespace WebwareTestIntegration\Admin;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
use Webware\Admin\Event\RegisterWidgetEvent;
use Webware\Admin\Middleware\DashboardMiddleware;
use Webware\Admin\Widget\AclWidgetFilterIterator;
use Webware\Admin\Widget\WidgetInterface;

use function array_key_exists;
use function iterator_to_array;

#[CoversClass(DashboardMiddleware::class)]
#[CoversClass(AclWidgetFilterIterator::class)]
#[CoversClass(RegisterWidgetEvent::class)]
final class DashboardMiddlewareIntegrationTest extends TestCase
{
    #[Test]
    public function itHidesDashboardWidgetsFromAnAuthenticatedMember(): void
    {
        $acl = new Acl();
        $acl->addRole('Administrator');
        $acl->addRole('Member');
        $acl->addResource('admin.dashboard');
        $acl->allow('Administrator', 'admin.dashboard', 'read');

        $box = new stdClass();
        $this->authenticateAndDispatch('member', ['Member'], $acl, $box);

        self::assertInstanceOf(AclWidgetFilterIterator::class, $box->widgets);
        self::assertCount(0, iterator_to_array($box->widgets));
    }

    #[Test]
    public function itShowsDashboardWidgetsToAnAuthenticatedAdministrator(): void
    {
        $acl = new Acl();
        $acl->addRole('Administrator');
        $acl->addResource('admin.dashboard');
        $acl->allow('Administrator', 'admin.dashboard', 'read');

        $box = new stdClass();
        $this->authenticateAndDispatch('administrator', ['Administrator'], $acl, $box);

        self::assertInstanceOf(AclWidgetFilterIterator::class, $box->widgets);
        self::assertCount(1, iterator_to_array($box->widgets));
    }

    /**
     * Authenticates a session-bound user through Mezzio's real PhpSession
     * adapter (no database required), then runs the dashboard middleware
     * against a real laminas-permissions-acl instance.
     */
    private function authenticateAndDispatch(
        string $identity,
        array $roles,
        Acl $acl,
        stdClass $box,
    ): void {
        $auth = new PhpSession(
            $this->createStub(UserRepositoryInterface::class),
            ['redirect' => '/login'],
            static fn(): ResponseInterface => new EmptyResponse(),
            $this->dualUserFactory(),
        );

        $session = $this->makeSession([
            UserInterface::class => [
                'username' => $identity,
                'roles'    => $roles,
                'details'  => [],
            ],
        ]);

        $request = new ServerRequest()->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $session);

        $user = $auth->authenticate($request);

        self::assertNotNull($user);
        self::assertInstanceOf(RoleInterface::class, $user);

        $widget = $this->makeWidget('admin.dashboard', 'read');

        $dispatcher = $this->createStub(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')
            ->willReturnCallback(
                static function (RegisterWidgetEvent $event) use ($widget): object {
                    $event->registerWidget($widget);

                    return $event;
                },
            );

        $handler = new class($box) implements RequestHandlerInterface {
            public function __construct(
                private readonly stdClass $box,
            ) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->box->widgets = $request->getAttribute(RegisterWidgetEvent::class);

                return new EmptyResponse();
            }
        };

        $middleware = new DashboardMiddleware($dispatcher, $acl);
        $middleware->process(
            $request->withAttribute(UserInterface::class, $user),
            $handler,
        );
    }

    /**
     * User factory bridging Mezzio's UserInterface to laminas' RoleInterface,
     * the seam webware-admin relies on for ACL filtering.
     *
     * @return callable(string, array, array): UserInterface
     */
    private function dualUserFactory(): callable
    {
        return static fn(string $identity, array $roles, array $details): UserInterface => new class(
            $identity,
            $roles,
        ) implements UserInterface, RoleInterface {
            public function __construct(
                private readonly string $identity,
                private readonly array $roles,
            ) {}

            public function getDetail(string $name, mixed $default = null): mixed
            {
                return $default;
            }

            public function getDetails(): array
            {
                return [];
            }

            public function getIdentity(): string
            {
                return $this->identity;
            }

            public function getRoleId(): string
            {
                return $this->roles[0];
            }

            public function getRoles(): iterable
            {
                return $this->roles;
            }
        };
    }

    private function makeSession(array $data): SessionInterface
    {
        return new class($data) implements SessionInterface {
            /** @var array<string, mixed> */
            private array $data;

            private bool $regenerated = false;

            /**
             * @param array<string, mixed> $data
             */
            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function clear(): void
            {
                $this->data = [];
            }

            public function get(string $name, mixed $default = null): mixed
            {
                return $this->data[$name] ?? $default;
            }

            public function has(string $name): bool
            {
                return array_key_exists($name, $this->data);
            }

            public function hasChanged(): bool
            {
                return false;
            }

            public function isRegenerated(): bool
            {
                return $this->regenerated;
            }

            public function regenerate(): self
            {
                $this->regenerated = true;

                return $this;
            }

            public function set(string $name, mixed $value): void
            {
                $this->data[$name] = $value;
            }

            public function toArray(): array
            {
                return $this->data;
            }

            public function unset(string $name): void
            {
                unset($this->data[$name]);
            }
        };
    }

    private function makeWidget(string $resourceId, string $privilege): WidgetInterface
    {
        return new class($resourceId, $privilege) implements WidgetInterface {
            public string $title {
                get => 'Test';
            }

            public string $template {
                get => 'test::widget';
            }

            public int $order {
                get => 0;
            }

            public function __construct(
                public string $resourceId,
                public string $privilege,
            ) {}

            public function getResourceId(): string
            {
                return $this->resourceId;
            }
        };
    }
}
