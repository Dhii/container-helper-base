<?php

namespace Dhii\Data\Container;

/**
 * Functionality for getting data from nested container.
 *
 * @since [*next-version*]
 */
trait ContainerGetPathCapableTrait
{
    /**
     * Retrieves a value from a nested container using provided path (list of segments).
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to read from.
     * @param array[string|StringableInterface]                 $path      The list of segments to retrieve value.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     *
     * @return mixed The value mapped to the given key.
     */
    protected function _containerGetPath($container, $path)
    {
        $service = $container;
        for ($i = 0; $i < count($path); $i++) {
            $service = $this->_containerGet($service, $path[$i]);
        }
        return $service;
    }

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
}
