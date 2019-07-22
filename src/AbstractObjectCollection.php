<?php declare(strict_types=1);

namespace Aeviiq\Collection;

use Aeviiq\Collection\Exception\InvalidArgumentException;
use Aeviiq\Collection\Exception\LogicException;

/**
 * @method \ArrayIterator|object[] getIterator
 * @method object|null first
 * @method object|null last
 */
abstract class AbstractObjectCollection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        array $elements = [],
        string $iteratorClass = \ArrayIterator::class
    ) {
        // We hardcode the flags here to prevent bugs that could occur when reflection is used on these collections.
        // For a more detailed explanation see https://github.com/aeviiq/collection/issues/19
        // The flags can still be changed using the setFlags() method, although this is not recommended.
        parent::__construct($elements, \ArrayObject::ARRAY_AS_PROPS, $iteratorClass);
    }

    /**
     * @return CollectionInterface|static
     */
    final public function exchangeArray($input): CollectionInterface
    {
        $newInput = [];
        foreach ($input as $index => $value) {
            $newInput[$this->createValidIndex($index, true)] = $value;
        }

        return parent::exchangeArray($newInput);
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetSet($index, $value): void
    {
        parent::offsetSet($this->createValidIndex($index, true), $value);
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetExists($index): bool
    {
        return parent::offsetExists($this->createValidIndex($index));
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetUnset($index): void
    {
        parent::offsetUnset($this->createValidIndex($index));
    }

    /**
     * {@inheritdoc}
     */
    final public function offsetGet($index)
    {
        return parent::offsetGet($this->createValidIndex($index));
    }

    final public function natcasesort(): void
    {
        throw new LogicException('natcasesort is not supported for objects.');
    }

    final public function natsort(): void
    {
        throw new LogicException('natsort is not supported for objects.');
    }

    /**
     * {@inheritdoc}
     */
    final protected function validateValue($value): void
    {
        if (!\is_object($value)) {
            throw InvalidArgumentException::expectedObject($this, \gettype($value));
        }

        $allowedInstance = $this->allowedInstance();
        if (!($value instanceof $allowedInstance)) {
            throw InvalidArgumentException::expectedInstance($this, $allowedInstance, \get_class($value));
        }
    }

    /**
     * @return string The allowed object instance the ObjectCollection supports.
     */
    abstract protected function allowedInstance(): string;

    /**
     * @param mixed $index
     * @param mixed $value
     *
     * @return string|int The index key which is valid depending on the setFlags.
     */
    protected function createValidIndex($index, bool $unique = false)
    {
        if (\ArrayObject::ARRAY_AS_PROPS !== ($this->getFlags() & \ArrayObject::ARRAY_AS_PROPS)) {
            return $index;
        }

        if (null === $index) {
            $index = 0;
        }

        if (!\is_numeric($index)) {
            return $index;
        }

        $newIndex = '_' . $index;
        if (!$unique) {
            return $newIndex;
        }

        while (isset($this->toArray()[$newIndex])) {
            $newIndex = '_' . $index++;
        }

        return $newIndex;
    }

    /**
     * @return CollectionInterface|static
     */
    protected function createFrom(array $elements): CollectionInterface
    {
        $instance = new static($elements, $this->getIteratorClass());
        $instance->setFlags($this->getFlags());

        return $instance;
    }
}
