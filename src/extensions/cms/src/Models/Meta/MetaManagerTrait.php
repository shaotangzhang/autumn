<?php
/**
 * Autumn PHP Framework
 *
 * Date:        14/05/2024
 */

namespace Autumn\Extensions\Cms\Models\Meta;

use Autumn\Attributes\Transient;
use Autumn\Database\Traits\ExtendedEntityManagerTrait;

trait MetaManagerTrait
{
    use ExtendedEntityManagerTrait;

    #[Transient]
    private ?array $meta = null;

    #[Transient]
    private ?array $temp = null;

    public function listOf(): iterable
    {
        $list = static::readonly()
            ->getBy([static::relation_primary_column() => $this->getPrimaryId()], ['name']);

        foreach ($list as $item) {
            $this->meta[$item->getName()] = $item;
        }

        return $this->meta;
    }

    public function valueOf(string $name): ?string
    {
        return ($this->meta[$name]
            ?? ($this->temp[$name] ??= static::findBy([static::relation_primary_column() => $this->getPrimaryId(), 'name' => $name]))
        )?->getValue();
    }

    public function groupOf(string $prefix): iterable
    {
        $list = static::readonly()
            ->where('name', 'LIKE', $prefix . '%')
            ->and(static::relation_primary_column(), $this->getPrimaryId())
            ->orderBy('name');

        $result = [];
        foreach ($list as $item) {
            $this->temp[$item->getName()] = $item;
            $result[] = $item;
        }

        return $result;
    }
}