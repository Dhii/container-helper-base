<?php

namespace Dhii\Data\Container;

use ArrayAccess;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use Iterator;
use OutOfRangeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Traversable;

/**
 * Common functionality for checking if a data set contains a specific key.
 *
 * Supported data sets are:
 * * Arrays
 * * Objects
 * * {@link \Psr\Container\ContainerInterface}
 * * {@link \ArrayAccess}
 * * {@link \Traversable}
 *
 * All SPL data storage classes are supported, given that they almost all implement either {@link \ArrayAccess} or
 * {@link \Traversable}.
 *
 * @since [*next-version*]
 */
trait ContainerHasCapableTrait
{
    /**
     * Retrieves an entry from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ContainerInterface $container The container or array to retrieve from.
     * @param string|Stringable        $key       The key of the value to retrieve.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     *
     * @return bool True if the container has an entry for the given key, false if not.
     */
    protected function _containerHas($container, $key)
    {
        $key = $this->_normalizeString($key);
        $isContainer = $container instanceof ContainerInterface;
        $isArrayLike = is_array($container) || $container instanceof ArrayAccess;
        $isObject = is_object($container);
        $isTraversable = $container instanceof Traversable;

        if (!$isContainer && !$isArrayLike && !$isObject && !$isTraversable) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument #1 is not a valid container'),
                null,
                null,
                $container
            );
        }

        if ($isContainer && $container->has($key)) {
            return true;
        }

        if ($isArrayLike && isset($container[$key])) {
            return true;
        }

        if ($isObject && property_exists($container, $key)) {
            return true;
        }

        if ($isTraversable) {
            try {
                /* @var $container Traversable */
                $iterator = $this->_resolveIterator($container);
                foreach ($iterator as $_key => $_value) {
                    if ($_key === $key) {
                        return true;
                    }
                }
            } catch (OutOfRangeException $outOfRangeException) {
                throw $this->_createContainerException(
                    $this->__('An error occurred while reading from the container'),
                    null,
                    $outOfRangeException
                );
            }
        }

        return false;
    }

    /**
     * Finds the deepest iterator that matches.
     *
     * @since [*next-version*]
     *
     * @param Traversable $iterator The iterator to resolve.
     * @param callable    $test     The test function which determines when the iterator is considered to be resolved.
     *                              Default: Returns `true` on first found instance of {@see Iterator}.
     * @param             $limit    int|float|string|Stringable The depth limit for resolution.
     *
     * @throws InvalidArgumentException If limit is not a valid integer representation.
     * @throws OutOfRangeException      If infinite recursion is detected, or the iterator could not be resolved within
     *                                  the depth limit.
     *
     * @return Iterator The inner-most iterator, or whatever the test function allows.
     */
    abstract protected function _resolveIterator(Traversable $iterator, $test = null, $limit = null);

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool|Stringable $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);

    /**
     * Creates a new container exception.
     *
     * @param string|Stringable|null     $message   The exception message, if any.
     * @param int|string|Stringable|null $code      The numeric exception code, if any.
     * @param RootException|null         $previous  The inner exception, if any.
     * @param ContainerInterface|null    $container The associated container, if any.
     *
     * @since [*next-version*]
     *
     * @return ContainerExceptionInterface The new exception.
     */
    abstract protected function _createContainerException(
        $message = null,
        $code = null,
        RootException $previous = null,
        ContainerInterface $container = null
    );

    /**
     * Creates a new invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
