<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\JOPhone;

/**
 * JO phone-number placeholder, mirroring {@see JOPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class JOPlaceholder extends BasePlaceholder
{
    /**
     * Build the JO placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('JO', $mask);
    }

    /**
     * Create a JOPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
