<?php

declare(strict_types=1);

namespace WebwareTest\Admin;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\Permissions\Acl\AclInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
use Webware\Admin\AclWidgetFilterIterator;
use Webware\Admin\Event\RegisterWidgetEvent;
use Webware\Admin\Middleware\DashboardMiddleware;
use Webware\Admin\WidgetInterface;

use function is_array;
use function iterator_to_array;

#[CoversClass(DashboardMiddleware::class)]
#[CoversClass(AclWidgetFilterIterator::class)]
final class DashboardMiddlewareTest extends TestCase
{
    #[Test]
    public function itAttachesAnEmptyIteratorWhenNoUserIsPresent(): void
    {
        $widget = $this->makeWidget('admin.dashboard', 'read');

        $acl = $this->createMock(AclInterface::class);
        $acl->expects(self::never())
            ->method('isAllowed');

        $box = new stdClass();
        $this->processRequest($widget, $acl, null, $box);

        self::assertInstanceOf(AclWidgetFilterIterator::class, $box->widgets);
        self::assertCount(0, iterator_to_array($box->widgets));
    }

    #[Test]
    public function itAttachesAnEmptyIteratorWhenTheUserIsNotRoleAware(): void
    {
        $widget = $this->makeWidget('admin.dashboard', 'read');

        $acl = $this->createMock(AclInterface::class);
        $acl->expects(self::never())
            ->method('isAllowed');

        // DefaultUser is Mezzio's shipped user type and does not implement
        // RoleInterface, so the dashboard must fail closed.
        $box = new stdClass();
        $this->processRequest($widget, $acl, new DefaultUser('administrator', ['Administrator']), $box);

        self::assertInstanceOf(AclWidgetFilterIterator::class, $box->widgets);
        self::assertCount(0, iterator_to_array($box->widgets));
    }

    #[Test]
    public function itAttachesWidgetsAllowedByTheAclToTheRequest(): void
    {
        $widget = $this->makeWidget('admin.dashboard', 'read');

        $acl = $this->createStub(AclInterface::class);
        $acl->method('isAllowed')->willReturn(true);

        $box = new stdClass();
        $this->processRequest($widget, $acl, $this->makeRoleAwareUser('administrator'), $box);

        self::assertInstanceOf(AclWidgetFilterIterator::class, $box->widgets);
        self::assertCount(1, iterator_to_array($box->widgets));
    }

    #[Test]
    public function itFiltersWidgetsDeniedByTheAcl(): void
    {
        $allowed = $this->makeWidget('admin.dashboard', 'read');
        $denied  = $this->makeWidget('admin.acl', 'read');

        $acl = $this->createStub(AclInterface::class);
        $acl->method('isAllowed')->willReturnCallback(
            static fn(mixed $role, mixed $resource, mixed $privilege): bool => 'admin.dashboard' === $resource,
        );

        $box = new stdClass();
        $this->processRequest([$allowed, $denied], $acl, $this->makeRoleAwareUser('administrator'), $box);

        $widgets = iterator_to_array($box->widgets, preserve_keys: false);
        self::assertCount(1, $widgets);
        self::assertSame('admin.dashboard', $widgets[0]->resourceId);
    }

    private function makeRoleAwareUser(string $identity): UserInterface
    {
        return new class($identity) implements UserInterface, RoleInterface {
            public function __construct(
                private readonly string $identity,
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
                return 'Administrator';
            }

            public function getRoles(): iterable
            {
                return ['Administrator'];
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

    /**
     * @param WidgetInterface|array<int, WidgetInterface> $widgets
     */
    private function processRequest(
        WidgetInterface|array $widgets,
        AclInterface $acl,
        ?UserInterface $user,
        stdClass $box,
    ): void {
        $dispatcher = $this->createStub(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')
            ->willReturnCallback(
                static function (RegisterWidgetEvent $event) use ($widgets): object {
                    foreach (is_array($widgets) ? $widgets : [$widgets] as $widget) {
                        $event->registerWidget($widget);
                    }

                    return $event;
                },
            );

        $request = new ServerRequest();
        if (null !== $user) {
            $request = $request->withAttribute(UserInterface::class, $user);
        }

        $handler = new class($box) implements RequestHandlerInterface {
            public function __construct(
                private readonly stdClass $box,
            ) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->box->widgets = $request->getAttribute(RegisterWidgetEvent::class);

                return new HtmlResponse('');
            }
        };

        new DashboardMiddleware($dispatcher, $acl)->process($request, $handler);
    }
}
