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
use stdClass;
use Traversable;

/**
 * Common functionality for reading data from data sets.
 *
 * Supported data sets are:
 * * Arrays
 * * stdClass
 * * {@link \Psr\Container\ContainerInterface}
 * * {@link \ArrayAccess}
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
     * @param array|stdClass|ContainerInterface $container The container to read from.
     * @param string|Stringable                 $key       The key of hte value to retrieve.
     *
     * @return mixed The value mapped to the given key.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     */
    protected function _containerGet($container, $key)
    {
        $key = $this->_normalizeString($key);
        $isContainer = $container instanceof ContainerInterface;
        $isArrayLike = is_array($container) || $container instanceof ArrayAccess;
        $isObject = is_object($container);

        if (!$isContainer && !$isArrayLike && !$isObject) {
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

        throw $this->_createNotFoundException($this->__('Key "%s" was not found', [$key]), null, null, null, $key);
    }

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
