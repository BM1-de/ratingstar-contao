<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RatingStarSealExtension extends Extension
{
    public function getAlias(): string
    {
        return 'rating_star_seal';
    }

    /**
     * @param array<array-key, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Expose the resolved base origin as a container parameter so the
        // settings resolver can fall back to it when a website does not set
        // its own rs_baseOrigin.
        $container->setParameter('ratingstar_seal.base_origin', $config['base_origin']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');
    }
}
