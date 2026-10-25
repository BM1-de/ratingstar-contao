<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\PageModel;
use RatingStar\ContaoSealBundle\Seal\SealSettingsResolver;
use RatingStar\ContaoSealBundle\Seal\SealVariants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * "RatingStar-Siegel" content element. Renders the official embed markup
 *
 *   <div class="rs-seal" data-slug="…" data-variant="…" [data-position="…"] [data-theme="…"]></div>
 *
 * and loads seal.js once per page (async, on demand — only because a seal is
 * actually rendered). The slug defaults to the website's root page setting and
 * can be overridden per element; every embed point offers both a slug override
 * and a variant/position choice (CONTRACT.md §13). The markup, the single
 * seal.js tag and the tier degrade are all left to seal.js — the bundle never
 * rebuilds HTML/CSS, the loader token, the pixel or the client JSON-LD itself
 * (CONTRACT.md §4).
 */
#[AsContentElement('rs_seal', category: 'includes', template: 'content_element/rs_seal')]
class SealController extends AbstractContentElementController
{
    public function __construct(private readonly SealSettingsResolver $resolver)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $page = $GLOBALS['objPage'] ?? null;
        $settings = $this->resolver->forPage($page instanceof PageModel ? $page : null);

        $slug = trim((string) $model->rs_slug);
        if ('' === $slug) {
            $slug = $settings->slug;
        }

        $variant = SealVariants::normalize((string) $model->rs_variant);

        // Empty choices mean "from the RatingStar settings" and emit nothing.
        $position = SealVariants::hasPosition($variant) ? SealVariants::normalizePosition((string) $model->rs_position) : '';
        $theme = SealVariants::hasTheme($variant) ? SealVariants::normalizeTheme((string) $model->rs_theme) : '';

        $template->set('has_slug', '' !== $slug);
        $template->set('slug', $slug);
        $template->set('variant', $variant);
        $template->set('variant_label', SealVariants::META[$variant]['label']);
        $template->set('position', '' !== $position ? $position : null);
        $template->set('theme', '' !== $theme ? $theme : null);
        $template->set('seal_js_url', $settings->sealJsUrl());
        $template->set('base_origin', $settings->baseOrigin);

        return $template->getResponse();
    }
}
