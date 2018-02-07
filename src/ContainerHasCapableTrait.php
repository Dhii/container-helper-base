<?php

namespace Dhii\Data\Container;

use ArrayAccess;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as BaseContainerInterface;
use stdClass;

/**
 * Common functionality for checking if a data set contains a specific key.
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
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable                  $key       The key of the value to retrieve.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     *
     * @return bool True if the container has an entry for the given key, false if not.
     */
    protected function _containerHas($container, $key)
    {
        $container = $this->_normalizeContainer($container);
        $key = $this->_normalizeKey($key);

        if ($container instanceof BaseContainerInterface) {
            return $container->has($key);
        }

        if ($container instanceof ArrayAccess) {
            // Catching exceptions thrown by `offsetExists()`
            try {
                return $container->offsetExists($key);
            } catch (RootException $e) {
                throw $this->_createContainerException($this->__('Could not check for key "%1$s"', [$key]), null, $e, null);
            }
        }

        if (is_array($container)) {
            return isset($container[$key]);
        }

        // Container is an `stdClass`
        return property_exists($container, $key);
    }

    /**
     * Normalizes a key.
     *
     * Treats it as one of many keys, throwing a more appropriate exception.
     *
     * @param string|int|float|bool|Stringable $key The key to normalize.
     *
     * @throws OutOfRangeException If key cannot be normalized.
     *
     * @return string The normalized key.
     */
    abstract protected function _normalizeKey($key);

    /**
     * Normalizes a container.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to normalize.
     *
     * @throws InvalidArgumentException If the container is invalid.
     *
     * @return array|ArrayAccess|stdClass|BaseContainerInterface Something that can be used with
     *                                                           {@see ContainerGetCapableTrait#_containerGet()} or
     *                                                           {@see ContainerHasCapableTrait#_containerHas()}.
     */
    abstract protected function _normalizeContainer($container);

    /**
     * Creates a new container exception.
     *
     * @param string|Stringable|null      $message   The exception message, if any.
     * @param int|string|Stringable|null  $code      The numeric exception code, if any.
     * @param RootException|null          $previous  The inner exception, if any.
     * @param BaseContainerInterface|null $container The associated container, if any.
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
