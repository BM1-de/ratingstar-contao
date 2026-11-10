<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use RatingStar\ContaoSealBundle\Seal\SealOptions;
use RatingStar\ContaoSealBundle\Seal\SealSettingsResolver;
use RatingStar\ContaoSealBundle\Seal\SealVariants;
use RatingStar\ContaoSealBundle\Seal\SealViewFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * "RatingStar seal" content element. The slug or group defaults to the
 * setting of the website's root page and can be overridden per element, as
 * can the variant, the position and the per-embed options.
 */
#[AsContentElement(self::TYPE, category: 'includes', template: 'content_element/ratingstar_seal')]
class SealController extends AbstractContentElementController
{
    public const TYPE = 'ratingstar_seal';

    public function __construct(
        private readonly SealSettingsResolver $resolver,
        private readonly SealViewFactory $viewFactory,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        // The back end preview has no current page: resolve the website via
        // the element's article instead.
        $page = $this->getPageModel();
        $settings = null !== $page ? $this->resolver->forPage($page) : $this->resolver->forContentElement($model);

        $variant = SealVariants::normalize((string) $model->ratingstar_variant);
        $options = [];

        // Only the options of the selected variant; values left over from
        // another variant stay in the database but are not rendered.
        foreach (SealOptions::keysForVariant($variant) as $key) {
            $options[$key] = $model->{SealOptions::column($key)};
        }

        $seal = $this->viewFactory->create($settings, $variant, [
            'slug' => (string) $model->ratingstar_slug,
            'group' => (string) $model->ratingstar_group,
            'position' => (string) $model->ratingstar_position,
            'static' => (bool) $model->ratingstar_static,
            'options' => $options,
            'attributes' => (string) $model->ratingstar_data_attributes,
        ]);

        // Nothing to show to visitors without a slug or group.
        if (null === $seal && !$this->isBackendScope($request)) {
            return new Response();
        }

        $template->set('seal', $seal);
        $template->set('variant', $variant);
        $template->set('variant_label_key', 'tl_content.ratingstar_variant_options.'.SealVariants::toStorage($variant));

        return $template->getResponse();
    }
}
