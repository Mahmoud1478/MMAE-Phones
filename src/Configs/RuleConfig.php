<?php

declare(strict_types=1);

namespace MMAE\Phones\Configs;

use MMAE\Phones\Base\BasePhoneRule;

/**
 * Immutable bundle of the five per-check configs resolved for one rule run.
 *
 * Assembled by {@see BasePhoneRule::validate()} from the
 * rule's fluent state and passed to the validation flow (and to any
 * {@see BasePhoneRule::validateUsing()} callback), which
 * reads each sub-config to decide what to enforce. Every `message` inside is a
 * raw `phones::` translation key — wrap it in `trans()` before failing.
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
