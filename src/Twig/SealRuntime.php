<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Twig;

use Contao\PageModel;
use RatingStar\ContaoSealBundle\Seal\SealSettingsResolver;
use RatingStar\ContaoSealBundle\Seal\SealVariants;
use RatingStar\ContaoSealBundle\Seal\SealViewFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

final class SealRuntime implements RuntimeExtensionInterface
{
    public const TEMPLATE = '@Contao/component/_ratingstar_seal.html.twig';

    public function __construct(
        private readonly SealSettingsResolver $resolver,
        private readonly SealViewFactory $viewFactory,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Renders a seal for the website of the current page. $config takes
     * "slug", "group", "position", "static", "options" (per-embed options by
     * their seal.js key, e.g. {carCount: 4}) and "attributes" (extra data
     * attributes as a string or map). Returns an empty string if neither a
     * slug nor a group is known.
     *
     * @param array<string, mixed> $config
     */
    public function render(Environment $twig, string $variant = SealVariants::DEFAULT, array $config = []): string
    {
        $seal = $this->viewFactory->create($this->resolver->forPage($this->getCurrentPage()), $variant, $config);

        if (null === $seal) {
            return '';
        }

        return $twig->render(self::TEMPLATE, ['seal' => $seal]);
    }

    private function getCurrentPage(): PageModel|null
    {
        foreach ([$this->requestStack->getCurrentRequest(), $this->requestStack->getMainRequest()] as $request) {
            $page = $request?->attributes->get('pageModel');

            if ($page instanceof PageModel) {
                return $page;
            }
        }

        return null;
    }
}
