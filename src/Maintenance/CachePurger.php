<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Maintenance;

use RatingStar\ContaoSealBundle\JsonLd\JsonLdProvider;

/**
 * "Purge the RatingStar data" job of the back end module System maintenance
 * (registered in contao/config/config.php). On the console the same is done
 * by: cache:pool:clear ratingstar_seal.cache
 */
class CachePurger
{
    public function __construct(private readonly JsonLdProvider $jsonLdProvider)
    {
    }

    public function purge(): void
    {
        $this->jsonLdProvider->clear();
    }
}
