<?php

declare(strict_types=1);

namespace MMAE\Phones\Configs;

use MMAE\Phones\Base\BasePhoneRule;

/**
 * Immutable bundle of the five per-check configs for one rule run, assembled by
 * {@see BasePhoneRule::validate()}. Every nested `message` is a raw `phones::`
 * translation key — wrap it in `trans()` before failing.
 *
 * @internal
 */
final readonly class RuleConfig
{
    public function __construct(
        public FormatConfig $format,
        public NullableConfig $nullable,
        public AllowEmptyConfig $allowEmpty,
        public ExistsConfig $exists,
        public UniqueConfig $unique,
    ) {}
}
