<?php declare(strict_types = 1);

namespace Aeviiq\Collection;

use Aeviiq\Collection\Exception\InvalidArgumentException;

/**
 * @method \ArrayIterator|int[] getIterator
 * @method int|null first
 * @method int|null last
 */
final class IntCollection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    public function offsetSet($index, $value): void
    {
        if (!\is_int($value)) {
            throw InvalidArgumentException::expectedInt($this, \gettype($value));
        }

        parent::offsetSet($index, $value);
    }
}
