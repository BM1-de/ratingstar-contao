<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\DependencyInjection;

use RatingStar\ContaoSealBundle\Seal\SealSettings;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Bundle configuration: the base origin every RatingStar URL is built from.
 * No host is written anywhere else in the bundle, so a staging or self-hosted
 * RatingStar is reached by changing this one value — for all websites here,
 * or per website on the root page (tl_page.ratingstar_base_origin).
 *
 *   ratingstar_seal:
 *       base_origin: 'https://ratingstar.de'
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(RatingStarSealExtension::ALIAS);

        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('base_origin')
                    ->info('Origin from which seal.js, widget data and JSON-LD are loaded.')
                    ->defaultValue('https://ratingstar.de')
                    ->cannotBeEmpty()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static fn (string $v): string => rtrim(trim($v), '/'))
                    ->end()
                    ->validate()
                        ->ifTrue(static fn (mixed $v): bool => !\is_string($v) || !SealSettings::isValidOrigin($v))
                        ->thenInvalid('base_origin must be an absolute http(s) origin without a path, e.g. https://reviews.example.com')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
