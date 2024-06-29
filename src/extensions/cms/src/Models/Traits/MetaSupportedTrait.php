<?php
/**
 * Autumn PHP Framework
 *
 * Date:        12/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Attributes\Transient;
use Autumn\Database\Db;
use Autumn\Database\Interfaces\Persistable;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Exceptions\SystemException;
use Autumn\Extensions\Cms\Models\Meta\MetaEntity;
use Autumn\Extensions\Cms\Models\Page\PageMeta;
use Autumn\System\Reflection;

trait MetaSupportedTrait /* implements MetaSupportedInterface */
{
    #[Transient]
    private ?array $meta = null;

    /**
     * @param string|null $lang
     */
    public function meta(string $lang = null): RepositoryInterface
    {
        static $queryCache;

        if (!isset($queryCache[static::class])) {
            $queryCache[static::class] = fn() => null;

            if ($metaRelation = defined($const = static::class . '::RELATION_META') ? constant($const) : null) {
                if (is_subclass_of($metaRelation, MetaEntity::class)) {
                    $primaryColumn = $metaRelation::relation_primary_column();
                    if (is_subclass_of($metaRelation, RepositoryInterface::class)) {
                        $queryCache[static::class] = fn(Persistable $instance) => $metaRelation::readonly()
                            ->and($primaryColumn, $instance->getId());
                    } else {
                        $queryCache[static::class] = fn(Persistable $instance) => Db::repository($metaRelation)
                            ->and($primaryColumn, $instance->getId());
                    }
                }
            }
        }

        $query = call_user_func($queryCache[static::class], $this);
        if (!$query) {
            throw SystemException::of(
                'The class `%s` has no relation with meta. Check the constant `RELATION_META` for detail.',
                static::class
            );
        }
        if ($lang !== null) {
            $query->and('lang', $lang);
        }

        return $query;
    }


    public function metadata(string $name, string $lang = null): ?string
    {
        if (!isset($this->meta[$lang])) {
            $this->meta[$lang] = [];
            foreach ($this->meta($lang) as $item) {
                $this->meta[$lang][$item->getName()] = $item;
            }
        }

        return isset($this->meta[$lang][$name])
            ? $this->meta[$lang][$name]->getValue()
            : null;
    }

    /**
     * @param string|null $prefix
     * @param string|null $lang
     * @return PageMeta[]
     */
    public function metaGroup(string $prefix = null, string $lang = null): iterable
    {
        if ($len = strlen($prefix ?? '')) {
            $group = [];
            if (!isset($this->meta[$lang])) {
                $this->meta[$lang] = [];
                foreach ($this->meta($lang) as $item) {
                    $this->meta[$lang][$name = $item->getName()] = $item;
                    if (str_starts_with($name, $prefix)) {
                        $group[substr($name, $len)] = $item->getValue();
                    }
                }
            } else {
                foreach ($this->meta[$lang] as $name => $item) {
                    if (str_starts_with($name, $prefix)) {
                        $group[substr($name, $len)] = $item->getValue();
                    }
                }
            }

            return $group;
        } else {
            if (!isset($this->meta[$lang])) {
                $this->meta[$lang] = [];
                foreach ($this->meta($lang) as $item) {
                    $this->meta[$lang][$name = $item->getName()] = $item;
                }
            }

            return $this->meta[$lang];
        }
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (is_string($offset) && $offset = trim($offset)) {
            if ($getter = Reflection::getter(static::class, $offset)) {
                try {
                    return $getter->invoke($this);
                } catch (\ReflectionException) {
                    return null;
                }
            }
        }

        return $this->metadata($offset);
    }
}