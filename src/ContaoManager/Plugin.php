<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use RatingStar\ContaoSealBundle\RatingStarSealBundle;

/**
 * Registers the bundle with the Contao Managed Edition. The bundle must load
 * after the Contao core bundle so its DCA overrides (tl_page, tl_content) and
 * content element take effect.
 */
class Plugin implements BundlePluginInterface
{
    /**
     * @return list<BundleConfig>
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(RatingStarSealBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
