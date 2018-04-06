<?php

namespace Dhii\Data\Container;

use ArrayAccess;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as BaseContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use Traversable;

/**
 * Common functionality for checking if a chain of nested containers contains a specific key.
 *
 * @since [*next-version*]
 */
trait ContainerHasPathCapableTrait
{
    /**
     * Check that path exists on a chain of nested containers.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to read from.
     * @param array|Traversable|stdClass                        $path      The key of the value to retrieve.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws OutOfRangeException         If the container or the key is invalid.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     *
     * @return bool True if the container has an entry for the given key, false if not.
     */
    protected function _containerHasPath($container, $path)
    {
        $path        = $this->_normalizeArray($path);
        $lastSegment = $path[count($path) - 1];

        $service = $container;

        foreach ($path as $segment) {
            $hasSegment = $this->_containerHas($service, $segment);
            if (!$hasSegment) {
                return false;
            } elseif ($segment !== $lastSegment) {
                $service = $this->_containerGet($service, $segment);
            }
        }

        return true;
    }

    /**
     * Normalizes a value into an array.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return array The normalized value.
     */
    abstract protected function _normalizeArray($value);

    /**
     * Retrieves a value from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable                  $key       The key of the value to retrieve.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     *
     * @return mixed The value mapped to the given key.
     */
    abstract protected function _containerGet($container, $key);

    /**
     * Check that container has required key.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable                  $key       The key of the value to retrieve.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws OutOfRangeException         If the container or the key is invalid.
     *
     * @return bool True if the container has an entry for the given key, false if not.
     */
    abstract protected function _containerHas($container, $key);
}
