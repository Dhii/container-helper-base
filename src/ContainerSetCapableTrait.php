<?php

namespace Dhii\Data\Container;

use ArrayAccess;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use stdClass;
use Traversable;
use Exception as RootException;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Functionality for setting data on a container.
 *
 * @since [*next-version*]
 */
trait ContainerSetCapableTrait
{
    /**
     * Sets data on the container.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass $container The container to set data on.
     * @param array|Traversable          $data      The data to set on the container.
     *
     * @throws ContainerExceptionInterface If error occurs while writing to container.
     */
    public function _containerSet(&$container, $data)
    {
        $data = $this->_normalizeIterable($data);

        if (is_array($container)) {
            foreach ($data as $_k => $_v) {
                $container[$_k] = $_v;
            }

            return;
        }

        if ($container instanceof ArrayAccess) {
            try {
                foreach ($data as $_k => $_v) {
                    $container->offsetSet($_k, $_v);
                }
            } catch (RootException $e) {
                throw $this->_createContainerException($this->__('Could not write to container'), null, $e);
            }

            return;
        }

        if ($container instanceof stdClass) {
            foreach ($data as $_k => $_v) {
                $container->{$_k} = $_v;
            }

            return;
        }
    }

    /**
     * Normalizes an iterable.
     *
     * Makes sure that the return value can be iterated over.
     *
     * @since [*next-version*]
     *
     * @param mixed $iterable The iterable to normalize.
     *
     * @throws InvalidArgumentException If the iterable could not be normalized.
     *
     * @return array|Traversable|stdClass The normalized iterable.
     */
    abstract protected function _normalizeIterable($iterable);

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
