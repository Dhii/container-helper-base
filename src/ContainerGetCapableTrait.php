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
use Psr\Container\NotFoundExceptionInterface;
use Traversable;

/**
 * Common functionality for reading data from data sets.
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
trait ContainerGetCapableTrait
{
    /**
     * Retrieves a value from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|object|Traversable|ContainerInterface $container The container to read from.
     * @param string|Stringable                           $key       The key of hte value to retrieve.
     *
     * @return mixed The value mapped to the given key.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     */
    protected function _containerGet($container, $key)
    {
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

        if ($isContainer) {
            return $container->get($key);
        }

        if ($isArrayLike && isset($container[$key])) {
            return $container[$key];
        }

        if ($isObject && property_exists($container, $key)) {
            return $container->{$key};
        }

        if ($isTraversable) {
            try {
                $iterator = $this->_resolveIterator($container);
                foreach ($iterator as $_key => $_value) {
                    if ($_key === $key) {
                        return $_value;
                    }
                }
            } catch (OutOfRangeException $outOfRangeException) {
                // Do nothing. The iterator was not resolved, so the key was not found.
            }
        }

        throw $this->_createNotFoundException($this->__('Key "%s" was not found', [$key]), null, null, null, $key);
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
     * Creates a new not found exception.
     *
     * @param string|Stringable|null     $message   The exception message, if any.
     * @param int|string|Stringable|null $code      The numeric exception code, if any.
     * @param RootException|null         $previous  The inner exception, if any.
     * @param ContainerInterface|null    $container The associated container, if any.
     * @param string|Stringable|null     $dataKey   The missing data key, if any.
     *
     * @since [*next-version*]
     *
     * @return NotFoundExceptionInterface The new exception.
     */
    abstract protected function _createNotFoundException(
        $message = null,
        $code = null,
        RootException $previous = null,
        ContainerInterface $container = null,
        $dataKey = null
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
