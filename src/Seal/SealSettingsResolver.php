<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Seal;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;

/**
 * Resolves the RatingStar settings of a website from its root page
 * (tl_page.ratingstar_*) and falls back to the bundle's base origin
 * (ratingstar_seal.base_origin) when the website does not set its own.
 *
 * The root page is Contao's "per website" level and multi-domain aware.
 */
class SealSettingsResolver
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly string $defaultBaseOrigin,
    ) {
    }

    public function getDefaultBaseOrigin(): string
    {
        return rtrim($this->defaultBaseOrigin, '/');
    }

    /**
     * Settings of the website a page belongs to. Without a page only the
     * bundle default applies (no slug, no key).
     */
    public function forPage(PageModel|null $page): SealSettings
    {
        return $this->fromRow($this->findRootPage($page)?->row() ?? []);
    }

    /**
     * Settings for a content element outside a front end page, i.e. in the
     * back end preview: element → article → page → root page.
     */
    public function forContentElement(ContentModel $element): SealSettings
    {
        return $this->forPage($this->findPageOfContentElement($element));
    }

    /**
     * Builds the settings from a tl_page row (or submitted values of one).
     *
     * @param array<string, mixed> $row
     */
    public function fromRow(array $row): SealSettings
    {
        $origin = rtrim(trim((string) ($row['ratingstar_base_origin'] ?? '')), '/');

        if (!SealSettings::isValidOrigin($origin)) {
            $origin = $this->getDefaultBaseOrigin();
        }

        return new SealSettings(
            trim((string) ($row['ratingstar_slug'] ?? '')),
            trim((string) ($row['ratingstar_group'] ?? '')),
            trim((string) ($row['ratingstar_api_key'] ?? '')),
            $origin,
            (bool) ($row['ratingstar_jsonld'] ?? false),
        );
    }

    public function findPageOfContentElement(ContentModel $element): PageModel|null
    {
        $this->framework->initialize();

        $table = (string) ($element->ptable ?: 'tl_article');
        $parentId = (int) $element->pid;

        // Nested elements: walk up to the outermost parent.
        for ($depth = 0; 'tl_content' === $table && $parentId > 0 && $depth < 10; ++$depth) {
            $parent = $this->framework->getAdapter(ContentModel::class)->findByPk($parentId);

            if (!$parent instanceof ContentModel) {
                return null;
            }

            $table = (string) ($parent->ptable ?: 'tl_article');
            $parentId = (int) $parent->pid;
        }

        if ('tl_article' !== $table || $parentId < 1) {
            return null;
        }

        $article = $this->framework->getAdapter(ArticleModel::class)->findByPk($parentId);

        if (!$article instanceof ArticleModel) {
            return null;
        }

        $page = $this->framework->getAdapter(PageModel::class)->findByPk((int) $article->pid);

        return $page instanceof PageModel ? $page : null;
    }

    private function findRootPage(PageModel|null $page): PageModel|null
    {
        if (null === $page) {
            return null;
        }

        if ('root' === $page->type) {
            return $page;
        }

        $rootId = (int) $page->loadDetails()->rootId;

        if ($rootId < 1) {
            return null;
        }

        $this->framework->initialize();

        $root = $this->framework->getAdapter(PageModel::class)->findByPk($rootId);

        return $root instanceof PageModel ? $root : null;
    }
}
