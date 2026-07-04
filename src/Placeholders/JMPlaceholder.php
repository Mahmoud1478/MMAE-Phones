<?php

declare(strict_types=1);

namespace MMAE\Phones\Placeholders;

use MMAE\Phones\Base\BasePlaceholder;
use MMAE\Phones\Phones\JMPhone;

/**
 * JM phone-number placeholder, mirroring {@see JMPhone}.
 *
 * @see BasePlaceholder::extract() for the resulting PlaceholderData.
 */
final class JMPlaceholder extends BasePlaceholder
{
    /**
     * Build the JM placeholder; $mask is the wildcard-digit character.
     */
    public function __construct(string $mask = 'X')
    {
        parent::__construct('JM', $mask);
    }

    /**
     * Create a JMPlaceholder using the given mask character.
     */
    public static function make(string $mask = 'X'): self
    {
        return new self($mask);
    }
}
