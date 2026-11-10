<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RatingStarSealExtension extends Extension implements PrependExtensionInterface
{
    public const ALIAS = 'ratingstar_seal';

    /**
     * Cache pool for the responses fetched from RatingStar (JSON-LD).
     */
    public const CACHE_POOL = 'ratingstar_seal.cache';

    public function getAlias(): string
    {
        return self::ALIAS;
    }

    /**
     * Declares the cache pool with the framework, which also registers it
     * for "cache:pool:clear ratingstar_seal.cache".
     */
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'cache' => [
                'pools' => [
                    self::CACHE_POOL => ['adapter' => 'cache.app'],
                ],
            ],
        ]);
    }

    /**
     * @param array<array-key, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        // Fallback for websites that do not set their own base origin on
        // the root page.
        $container->setParameter('ratingstar_seal.base_origin', $config['base_origin']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');
    }
}
