<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Bundle configuration. Defines the global base origin from which every
 * RatingStar URL is built (CONTRACT.md §2: one setting, no hardcoded host).
 *
 * The default points at the production origin. A self-hosted or staging
 * RatingStar instance is reached by overriding this single value — either
 * globally here or per website on the root page (tl_page.rs_baseOrigin).
 *
 *   rating_star_seal:
 *       base_origin: 'https://ratingstar.de'
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('rating_star_seal');

        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('base_origin')
                    ->info('Origin from which seal.js, JSON-LD and widget data are loaded.')
                    ->defaultValue('https://ratingstar.de')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(static fn ($v): bool => !\is_string($v) || !preg_match('#^https?://[^/]+$#', rtrim((string) $v, '/')))
                        ->thenInvalid('base_origin must be an absolute http(s) origin without a trailing path, e.g. https://ratingstar.de')
                    ->end()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static fn (string $v): string => rtrim($v, '/'))
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
