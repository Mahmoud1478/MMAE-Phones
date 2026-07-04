<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\VNPhone;

/**
 * VN phone-number placeholder, mirroring {@see VNPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class VNPlaceholder extends BasePlaceholder
{
    /**
     * Build the VN placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('VN', $mask);
    }

    /**
     * Create a VNPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
