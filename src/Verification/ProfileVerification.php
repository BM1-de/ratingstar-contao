<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Verification;

/**
 * Result of a slug check.
 */
final class ProfileVerification
{
    /** The profile page exists; $name holds the name from its title. */
    public const FOUND = 'found';

    /** The profile page answered with 404. */
    public const NOT_FOUND = 'not_found';

    /** The check could not be completed (timeout, network, other status). */
    public const UNAVAILABLE = 'unavailable';

    public function __construct(
        public readonly string $status,
        public readonly string $url,
        public readonly string|null $name = null,
        public readonly string|null $detail = null,
    ) {
    }

    public function isFound(): bool
    {
        return self::FOUND === $this->status;
    }
}
