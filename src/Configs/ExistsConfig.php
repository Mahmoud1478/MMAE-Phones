<?php

declare(strict_types=1);

namespace MMAE\Phones\Configs;

use MMAE\Phones\Base\BasePhone;

/**
 * Existence-check settings for a single rule run.
 *
 * Part of {@see RuleConfig}. When `enabled`, the number must already exist in
 * `table`.`column`, matched against every accepted shape
 * ({@see BasePhone::all()}). Disabled by default so the
 * check always passes.
 *
 * @internal
 */
final readonly class ExistsConfig
{
    /**
     * @param  bool  $enabled  whether the existence check applies
     * @param  string  $table  table the number must exist in
     * @param  string|null  $column  column to match; defaults to the attribute name when null
     * @param  string  $message  translation key used when the number is not found
     */
    public function __construct(
        public bool $enabled,
        public string $table,
        public ?string $column,
        public string $message,
    ) {}
}
