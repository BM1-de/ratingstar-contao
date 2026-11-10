<?php

declare(strict_types=1);

namespace RatingStar\ContaoSealBundle\Seal;

/**
 * Whose seal is shown: a single branch (profile slug, /t/<slug>) or a whole
 * group (group slug, /g/<group>). An element carries either data-slug or
 * data-group, never both.
 */
final class SealSubject
{
    public function __construct(
        public readonly string $id,
        public readonly bool $isGroup,
    ) {
    }

    public function attributeName(): string
    {
        return $this->isGroup ? 'data-group' : 'data-slug';
    }

    /**
     * Public profile page, also the target of the slug check.
     */
    public function profilePath(): string
    {
        return ($this->isGroup ? '/g/' : '/t/').rawurlencode($this->id);
    }

    /**
     * Static SVG seal; $svgVariant is one of banner|circle|card.
     */
    public function staticSvgPath(string $svgVariant): string
    {
        return ($this->isGroup ? '/seal/g/' : '/seal/').rawurlencode($this->id).'.svg?variant='.rawurlencode($svgVariant);
    }
}
