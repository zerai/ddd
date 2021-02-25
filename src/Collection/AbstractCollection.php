<?php

declare(strict_types=1);

namespace CNastasi\DDD\Collection;

use ArrayObject;
use Closure;
use CNastasi\DDD\Contract\Collection;
use CNastasi\DDD\Contract\ValueObject;
use CNastasi\DDD\Error\UnsupportedCollectionItem;
use Traversable;

/**
 * @template K
 * @template T of ValueObject
 *
 * @implements Collection<K, T>
 */
abstract class AbstractCollection implements Collection
{
    /**
     * @property ArrayObject<K, T>
     */
    protected ArrayObject $collection;

    final public function __construct()
    {
        $this->collection = new ArrayObject();
    }

    public function addItem(ValueObject $item): void
    {
        if (!$this->typeIsSupported($item)) {
            throw new UnsupportedCollectionItem(\get_class($item), $this->getItemType());
        }

        $this->collection->append($item);
    }

    /**
     * @param T $item
     *
     * @return bool
     */
    private function typeIsSupported($item): bool
    {
        return \is_a($item, $this->getItemType(), true);
    }

    abstract public function getItemType(): string;

    public function getIterator(): Traversable
    {
        return $this->collection->getIterator();
    }

    /**
     * @param Closure(T):bool $filterFunction
     *
     * @return static
     */
    public function filter(Closure $filterFunction): self
    {
        $collection = new static();

        foreach ($this as $item) {
            if ($filterFunction($item)) {
                $collection->addItem($item);
            }
        }

        return $collection;
    }

    public function walk(Closure $filterFunction): void
    {
        foreach ($this as $item) {
            $filterFunction($item);
        }
    }

    public function count(): int
    {
        return $this->collection->count();
    }

    /**
     * @inheritdoc
     */
    public function has(ValueObject $item): bool
    {
        foreach ($this->collection as $element) {
            if ($element->equalsTo($item)) {
                return true;
            }
        }

        return false;
    }

    public function hasKey($key): bool
    {
        return $this->collection->offsetExists($key);
    }

    /**
     * @param array $array
     *
     * @return static
     */
    public static function fromArray(array $array): self
    {
        $collection = new static();

        foreach ($array as $item) {
            $collection->addItem($item);
        }

        return $collection;
    }

    public function first(): ?ValueObject
    {
        $first = \reset($this->collection);

        return $first === false ? null : $first;
    }

    public function get($key): ?ValueObject
    {
        return $this->collection->offsetGet($key);
    }
}
