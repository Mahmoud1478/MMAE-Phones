<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\WSPhone;

/**
 * WS phone-number placeholder, mirroring {@see WSPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class WSPlaceholder extends BasePlaceholder
{
    /**
     * Build the WS placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('WS', $mask);
    }

    /**
     * Create a WSPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
