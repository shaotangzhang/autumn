<?php
/**
 * Autumn PHP Framework
 *
 * Date:        8/05/2024
 */

namespace Autumn\Lang;

use Autumn\Interfaces\ArrayInterface;
use Generator;

class IteratorAdaptor implements \IteratorAggregate, ArrayInterface
{
    private iterable $list;
    private array $cache = [];
    private bool $done = false;

    public function __construct(iterable $list)
    {
        if (is_array($list)) {
            $this->cache = $list;
            $this->done = true;
        } else {
            $this->list = $list;
        }
    }

    public function getIterator(): Generator
    {
        if ($this->done) {
            yield from $this->cache;
        } else {
            foreach ($this->list as $name => $value) {
                $this->cache[$name] = $value;
                yield $name => $value;
            }
            $this->done = true;
        }
    }

    public function toArray(): array
    {
        if (!$this->done) {
            foreach ($this as $name => $value) {
                $this->cache[$name] = $value;
            }
            $this->done = true;
        }

        return $this->cache;
    }
}
