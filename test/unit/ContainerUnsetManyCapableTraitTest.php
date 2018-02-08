<?php

namespace Dhii\Data\Container\UnitTest;

use ArrayObject;
use Dhii\Data\Container\ContainerUnsetManyCapableTrait as TestSubject;
use InvalidArgumentException;
use OutOfRangeException;
use Psr\Container\ContainerExceptionInterface;
use ReflectionMethod;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ContainerUnsetManyCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Data\Container\ContainerUnsetManyCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            '__',
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->setMethods($methods)
            ->disableArgumentCloning()
            ->enableProxyingToOriginalMethods()
            ->getMockForTrait();

        $mock->method('__')
                ->will($this->returnArgument(0));

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string $className      Name of the class for the mock to extend.
     * @param string $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockBuilder The builder for a mock of an object that extends and implements
     *                     the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf('abstract class %1$s extends %2$s implements %3$s {}', [
            $paddingClassName,
            $className,
            implode(', ', $interfaceNames),
        ]);
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Not Found exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return MockObject|RootException|InvalidArgumentException The new exception.
     */
    public function createInvalidArgumentException($message = '')
    {
        $mock = $this->getMockBuilder('InvalidArgumentException')
            ->setConstructorArgs([$message])
            ->getMockForAbstractClass();

        return $mock;
    }

    /**
     * Creates a new Out of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return MockObject|RootException|OutOfRangeException The new exception.
     */
    public function createOutOfRangeException($message = '')
    {
        $mock = $this->getMockBuilder('OutOfRangeException')
            ->setConstructorArgs([$message])
            ->getMockForAbstractClass();

        return $mock;
    }

    /**
     * Creates a new Container exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return MockObject|RootException|ContainerExceptionInterface The new exception.
     */
    public function createContainerException($message = '')
    {
        $mock = $this->mockClassAndInterfaces('Exception', ['Psr\Container\ContainerExceptionInterface'])
            ->getMockForAbstractClass();

        $mock->method('getMessage')
            ->will($this->returnValue($message));

        return $mock;
    }

    /**
     * Creates a new `ArrayAccess` instance.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     * @param array $data    The data for array access.
     *
     * @return MockObject|ArrayObject
     */
    public function createArrayAccess($methods = [], $data = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, []);

        $mock = $this->getMockBuilder('ArrayObject')
            ->setMethods($methods)
            ->setConstructorArgs($data)
            ->enableProxyingToOriginalMethods()
            ->getMock();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests that `_containerUnsetMany()` works as expected.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetMany()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $keys = [$key1, $key3];
        $container = $this->createArrayAccess();
        $subject = $this->createInstance();

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($keys)
            ->will($this->returnValue($keys));

        $subject->expects($this->exactly(count($keys)))
            ->method('_containerUnset')
            ->withConsecutive(
                [$container, $key1],
                [$container, $key3]
            );

        $reflection = new ReflectionMethod($subject, '_containerUnsetMany');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $keys]);
    }

    /**
     * Tests that `_containerUnsetMany()` fails correctly when given an invalid key list.
     *
     * @since [*next-version*]
     */
    public function testContainerSetManyFailureInvalidData()
    {
        $keys = uniqid('keys');
        $container = $this->createArrayAccess();
        $exception = $this->createInvalidArgumentException('Invalid key list');
        $subject = $this->createInstance(['_normalizeIterable']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($keys)
            ->will($this->throwException($exception));

        $this->setExpectedException('InvalidArgumentException');
        $reflection = new ReflectionMethod($subject, '_containerUnsetMany');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $keys]);
    }

    /**
     * Tests that `_containerUnsetMany()` fails correctly when given an invalid container.
     *
     * @since [*next-version*]
     */
    public function testContainerSetManyFailureInvalidContainer()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $keys = [$key1, $key3];
        $container = $this->createArrayAccess();
        $exception = $this->createInvalidArgumentException('Invalid container');
        $subject = $this->createInstance(['_containerUnset']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($keys)
            ->will($this->returnValue($keys));
        $subject->expects($this->exactly(1))
            ->method('_containerUnset')
            ->with($container, $key1)
            ->will($this->throwException($exception));

        $this->setExpectedException('InvalidArgumentException');
        $reflection = new ReflectionMethod($subject, '_containerUnsetMany');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $keys]);
    }

    /**
     * Tests that `_containerUnsetMany()` fails correctly when one of the data keys is invalid.
     *
     * @since [*next-version*]
     */
    public function testContainerSetManyFailureInvalidKey()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $keys = [$key1, $key3];
        $container = $this->createArrayAccess();
        $exception = $this->createOutOfRangeException('Invalid key');
        $subject = $this->createInstance(['_containerUnset']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($keys)
            ->will($this->returnValue($keys));
        $subject->expects($this->exactly(1))
            ->method('_containerUnset')
            ->with($container, $key1)
            ->will($this->throwException($exception));

        $this->setExpectedException('OutOfRangeException');
        $reflection = new ReflectionMethod($subject, '_containerUnsetMany');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $keys]);
    }

    /**
     * Tests that `_containerUnsetMany()` fails correctly when trying to access the container.
     *
     * @since [*next-version*]
     */
    public function testContainerSetManyFailureContainerAccess()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $keys = [$key1, $key3];
        $container = $this->createArrayAccess();
        $exception = $this->createContainerException('Could not access container');
        $subject = $this->createInstance(['_containerUnset']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($keys)
            ->will($this->returnValue($keys));
        $subject->expects($this->exactly(1))
            ->method('_containerUnset')
            ->with($container, $key1)
            ->will($this->throwException($exception));

        $this->setExpectedException('Psr\Container\ContainerExceptionInterface');
        $reflection = new ReflectionMethod($subject, '_containerUnsetMany');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $keys]);
    }
}
