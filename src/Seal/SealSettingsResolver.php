<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Seal;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;

/**
 * Resolves the effective RatingStar settings for a given front end page by
 * reading the per-website fields off its root page (tl_page.rs_*) and falling
 * back to the bundle's global base origin when a website does not set its own.
 *
 * Per-website (root page) is Contao's "per site/tenant" level (CONTRACT.md §1)
 * and is multi-domain aware.
 */
class SealSettingsResolver
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly string $defaultBaseOrigin,
    ) {
    }

    public function forPage(PageModel|null $page): SealSettings
    {
        $root = $this->resolveRootPage($page);

        $slug = trim((string) ($root->rs_slug ?? ''));
        $apiKey = trim((string) ($root->rs_apiKey ?? ''));
        $origin = trim((string) ($root->rs_baseOrigin ?? ''));

        if ('' === $origin) {
            $origin = $this->defaultBaseOrigin;
        }

        return new SealSettings($slug, $apiKey, rtrim($origin, '/'));
    }

    private function resolveRootPage(PageModel|null $page): PageModel|null
    {
        if (null === $page) {
            return null;
        }

        if ('root' === $page->type) {
            return $page;
        }

        $page->loadDetails();
        $rootId = (int) $page->rootId;

        if ($rootId < 1) {
            return null;
        }

        $this->framework->initialize();

        /** @var PageModel $adapter */
        $adapter = $this->framework->getAdapter(PageModel::class);

        return $adapter->findByPk($rootId);
    }
}
