<?php

namespace Dhii\Data\Container\UnitTest;

use ArrayObject;
use Dhii\Data\Container\ContainerSetCapableTrait as TestSubject;
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
class ContainerSetCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Data\Container\ContainerSetCapableTrait';

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
     * @return MockObject|ContainerExceptionInterface The new exception.
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
     * Tests that `_containerSet()` works as expected when writing to an array.
     *
     * @since [*next-version*]
     */
    public function testContainerSetArray()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $initialData = [uniqid('key') => uniqid('val')];
        $container = $initialData;
        $subject = $this->createInstance(['_normalizeKey']);

        $subject->expects($this->exactly(count($data)))
            ->method('_normalizeKey')
            ->with($key)
            ->will($this->returnValue((string) $key));

        $reflection = new ReflectionMethod($subject, '_containerSet');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key, $val]);

        $this->assertEquals(array_merge($initialData, $data), $container, 'Modified state is wrong');
    }

    /**
     * Tests that `_containerSet()` works as expected when writing to an `stcClass` object.
     *
     * @since [*next-version*]
     */
    public function testContainerSetStdClass()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $initialData = [uniqid('key') => uniqid('val')];
        $container = (object) $initialData;
        $subject = $this->createInstance(['_normalizeKey']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(count($data)))
            ->method('_normalizeKey')
            ->with($key)
            ->will($this->returnValue((string) $key));

        $reflection = new ReflectionMethod($subject, '_containerSet');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key, $val]);
        $this->assertEquals((object) array_merge($initialData, $data), $container, 'Modified state is wrong');
    }

    /**
     * Tests that `_containerSet()` works as expected when writing to an `ArrayAccess` object.
     *
     * @since [*next-version*]
     */
    public function testContainerSetArrayAccess()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $initialData = [uniqid('key') => uniqid('val')];
        $container = $this->createArrayAccess(['offsetSet'], [$initialData]);
        $subject = $this->createInstance(['_normalizeKey']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key)
            ->will($this->returnValue((string) $key));

        $container->expects($this->exactly(1))
            ->method('offsetSet')
            ->withConsecutive([$key, $val]);

        $reflection = new ReflectionMethod($subject, '_containerSet');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key, $val]);
        // Container does not get modified because the method is not proxying to the original, and thus
        // the state of the original container is not modified. However, we can still know that everything is correct
        // because `offsetSet()` is being invoked correctly. Testing of the result can be done in a functional test.
        // $this->assertEquals(array_merge($initialData, $data), iterator_to_array($container), 'Modified state is wrong');
    }

    /**
     * Tests that `_containerSet()` fails as expected when writing to an `ArrayAccess` object.
     *
     * @since [*next-version*]
     */
    public function testContainerSetArrayAccessFailureOffsetSet()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $initialData = [uniqid('key') => uniqid('val')];
        $container = $this->createArrayAccess(['offsetSet'], [$initialData]);
        $innerException = $this->createException('Error in `offsetSet()`');
        $exception = $this->createContainerException('Could not write to container');
        $subject = $this->createInstance(['_normalizeKey']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key)
            ->will($this->returnValue((string) $key));

        $subject->expects($this->exactly(1))
            ->method('_createContainerException')
            ->with(
                $this->isType('string'),
                null,
                $innerException,
                null
            )
            ->will($this->returnValue($exception));

        $container->expects($this->exactly(count($data)))
            ->method('offsetSet')
            ->withConsecutive([$key, $val])
            ->will($this->throwException($innerException));

        $this->setExpectedException('Psr\Container\ContainerExceptionInterface');
        $reflection = new ReflectionMethod($subject, '_containerSet');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key, $val]);
    }

    /**
     * Tests that `_containerSet()` fails correctly when given an invalid container.
     *
     * @since [*next-version*]
     */
    public function testContainerSetFailureInvalidContainer()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key, $val];
        $container = uniqid('container');
        $exception = $this->createInvalidArgumentException('Invalid container');
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_createInvalidArgumentException')
            ->with(
                $this->isType('string'),
                null,
                null,
                $container
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('InvalidArgumentException');
        $reflection = new ReflectionMethod($subject, '_containerSet');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key, $val]);
    }

    /**
     * Tests that `_containerSet()` fails correctly when given an invalid key.
     *
     * @since [*next-version*]
     */
    public function testContainerSetFailureInvalidKey()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key, $val];
        $container = $data;
        $exception = $this->createOutOfRangeException('Invalid key');
        $subject = $this->createInstance(['_normalizeKey']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key)
            ->will($this->throwException($exception));

        $this->setExpectedException('OutOfRangeException');
        $reflection = new ReflectionMethod($subject, '_containerSet');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key, $val]);
    }
}
