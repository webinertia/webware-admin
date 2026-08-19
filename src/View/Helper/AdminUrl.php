<?php

declare(strict_types=1);

namespace Webware\Admin\View\Helper;

use InvalidArgumentException;
use Laminas\View\Helper\StatefulHelperInterface;
use Mezzio\Helper\Exception\RuntimeException as UrlHelperRuntimeException;
use Mezzio\Helper\UrlHelper;
use Override;
use Psl\Type;
use Psl\Type\Exception\CoercionException;

final readonly class AdminUrl implements StatefulHelperInterface
{
    public function __construct(
        private UrlHelper $urlHelper,
        private string $routeNamePrefix,
    ) {}

    #[Override]
    public function resetState(): void {}

    /**
     * @param array<string, mixed> $routeParams
     * @param array<string, mixed> $queryParams
     * @param array{'reuse_query_params'?: bool, 'reuse_result_params'?: bool, 'router'?: array<array-key, mixed>} $options
     * @throws InvalidArgumentException
     * @throws UrlHelperRuntimeException
     * @throws CoercionException
     */
    public function __invoke(
        string $routeName,
        array $routeParams = [],
        array $queryParams = [],
        ?string $fragmentIdentifier = null,
        array $options = [],
    ): string {
        $route = Type\non_empty_string()->coerce($this->routeNamePrefix . $routeName);

        return ($this->urlHelper)(
            $route,
            $routeParams,
            $queryParams,
            $fragmentIdentifier,
            $options,
        );
    }
}
