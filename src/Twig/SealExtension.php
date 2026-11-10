<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Registers ratingstar_seal() for theme developers — the embed point for
 * layouts and other Twig templates, with the same options as the content
 * element:
 *
 *   {{ ratingstar_seal('bar') }}
 *   {{ ratingstar_seal('profile-card', {position: 'bottom-right', options: {pcColor: 'weiss'}}) }}
 *   {{ ratingstar_seal('carousel', {slug: 'other-branch', attributes: 'data-car-count="4"'}) }}
 *   {{ ratingstar_seal('seal-circle', {group: 'my-group', static: true}) }}
 */
final class SealExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ratingstar_seal',
                [SealRuntime::class, 'render'],
                ['needs_environment' => true, 'is_safe' => ['html']],
            ),
        ];
    }
}
