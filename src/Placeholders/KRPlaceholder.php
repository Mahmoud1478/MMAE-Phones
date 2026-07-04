<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\KRPhone;

/**
 * KR phone-number placeholder, mirroring {@see KRPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class KRPlaceholder extends BasePlaceholder
{
    /**
     * Build the KR placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('KR', $mask);
    }

    /**
     * Create a KRPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
