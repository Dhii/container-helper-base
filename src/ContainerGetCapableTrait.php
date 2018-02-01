<?php

namespace Dhii\Data\Container;

use ArrayAccess;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

/**
 * Common functionality for reading data from data sets.
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
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable              $key       The key of the value to retrieve.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     *
     * @return mixed The value mapped to the given key.
     */
    protected function _containerGet($container, $key)
    {
        $container = $this->_normalizeContainer($container);
        $origKey   = $key;
        $key       = $this->_normalizeString($key);
        // NotFoundExceptionInterface#getDataKey() returns `string` or `Stringable`,
        // so normalize only other types, and preserve original
        $origKey = is_string($origKey) || $origKey instanceof Stringable
            ? $origKey
            : $key;

        if ($container instanceof ContainerInterface) {
            try {
                return $container->get($key);
            } catch (NotFoundExceptionInterface $e) {
                throw $this->_createNotFoundException($this->__('Key not found'), null, $e, null, $origKey);
            }
        }

        if ($container instanceof ArrayAccess) {
            // Catching exceptions thrown by `offsetExists()`
            try {
                $hasKey = isset($container[$key]);
            } catch (RootException $e) {
                throw $this->_createContainerException($this->__('Could not check for key "%1$s"', [$key]), null, $e, null);
            }

            if (!$hasKey) {
                throw $this->_createNotFoundException($this->__('Key not found'), null, null, null, $origKey);
            }

            // Catching exceptions thrown by `offsetGet()`
            try {
                return $container[$key];
            } catch (RootException $e) {
                throw $this->_createContainerException($this->__('Could not retrieve value'), null, $e, null);
            }
        }

        if (is_array($container)) {
            if (!isset($container[$key])) {
                throw $this->_createNotFoundException($this->__('Key not found'), null, null, null, $origKey);
            }

            return $container[$key];
        }

        // Container is an `stdClass`
        if (!property_exists($container, $key)) {
            throw $this->_createNotFoundException($this->__('Key not found'), null, null, null, $origKey);
        }

        return $container->{$key};
    }

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see Stringable).
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
     * Normalizes a container.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to normalize.
     *
     * @throws InvalidArgumentException If the container is invalid.
     *
     * @return array|ArrayAccess|stdClass|ContainerInterface Something that can be used with
     *                                                       {@see ContainerGetCapableTrait#_containerGet()} or
     *                                                       {@see ContainerHasCapableTrait#_containerHas()}.
     */
    abstract protected function _normalizeContainer($container);

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
