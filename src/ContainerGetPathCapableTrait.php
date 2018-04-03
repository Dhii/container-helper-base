<?php

namespace Dhii\Data\Container;

use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface as BaseContainerInterface;

/**
 * Functionality for getting data from nested container.
 *
 * @since [*next-version*]
 */
trait ContainerGetPathCapableTrait
{
    /**
     * Retrieves a value from a chain of nested containers by path.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The top container in the chain to read from.
     * @param string[]|Stringable[]                             $path      The list of path segments.
     *
     * @throws InvalidArgumentException    If one of the containers in the chain is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from one of the containers in the chain.
     * @throws NotFoundExceptionInterface  If one of the containers in the chain does not have the corresponding key.
     *
     * @return mixed The value at the specified path.
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
