<?php

declare(strict_types=1);

namespace MMAE\Phones\Configs;

use MMAE\Phones\Base\BasePhone;

/**
 * Uniqueness-check settings for a single rule run.
 *
 * Part of {@see RuleConfig}. When `enabled`, the number must not already exist
 * in `table`.`column` (matched against every accepted shape via
 * {@see BasePhone::all()}), optionally excluding one record.
 * Disabled by default so the check always passes.
 *
 * @internal
 */
final readonly class UniqueConfig
{
    /**
     * @param  bool  $enabled  whether the uniqueness check applies
     * @param  string  $table  table the number must be unique in
     * @param  string|null  $column  column to match; defaults to the attribute name when null
     * @param  mixed  $ignore  record id to exclude, e.g. the current row on update
     * @param  string  $ignoreColumn  column the `$ignore` id is matched on
     * @param  string  $message  translation key used when the number already exists
     */
    public function __construct(
        public bool $enabled,
        public string $table,
        public ?string $column,
        public mixed $ignore,
        public string $ignoreColumn,
        public string $message,
    ) {}
}
