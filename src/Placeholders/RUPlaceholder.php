<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\RUPhone;

/**
 * RU phone-number placeholder, mirroring {@see RUPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class RUPlaceholder extends BasePlaceholder
{
    /**
     * Build the RU placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('RU', $mask);
    }

    /**
     * Create a RUPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
