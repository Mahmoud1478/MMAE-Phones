<?php

declare(strict_types=1);

namespace MMAE\Phones\Configs;

/**
 * Null-handling settings for a single rule run.
 *
 * Part of {@see RuleConfig}. When `enabled`, a `null` value passes and skips
 * every other check; otherwise a present `null` fails with `message`.
 *
 * @internal
 */
final readonly class NullableConfig
{
    /**
     * @param  bool  $enabled  when true, a null value passes and skips every other check
     * @param  string  $message  translation key used when a null value is rejected
     */
    public function __construct(
        public bool $enabled,
        public string $message,
    ) {}
}
