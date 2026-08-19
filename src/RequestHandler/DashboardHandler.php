<?php

declare(strict_types=1);

namespace Webware\Admin\RequestHandler;

use Laminas\Diactoros\Exception\InvalidArgumentException;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webware\Admin\Event\RegisterWidgetEvent;

final class DashboardHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly TemplateRendererInterface $template,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse($this->template->render('admin::dashboard', [
            'widgets' => $request->getAttribute(RegisterWidgetEvent::class, []),
        ]));
    }
}
