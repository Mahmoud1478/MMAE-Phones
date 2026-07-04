<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\ITPhone;

/**
 * IT phone-number placeholder, mirroring {@see ITPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class ITPlaceholder extends BasePlaceholder
{
    /**
     * Build the IT placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('IT', $mask);
    }

    /**
     * Create a ITPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
