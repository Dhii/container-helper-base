<?php

namespace Dhii\Data\Container\UnitTest;

use ArrayObject;
use Dhii\Data\Container\ContainerUnsetCapableTrait as TestSubject;
use InvalidArgumentException;
use OutOfRangeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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
class ContainerUnsetCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Data\Container\ContainerUnsetCapableTrait';

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
            ->will($this->returnCallback(function ($string, $values) {
                return vsprintf($string, $values);
            }));

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
     * Creates a new Not Found exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return MockObject|RootException|NotFoundExceptionInterface The new exception.
     */
    public function createNotFoundException($message = '')
    {
        $mock = $this->mockClassAndInterfaces('Exception', ['Psr\Container\NotFoundExceptionInterface'])
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
     * Tests that `_containerUnset()` works as expected when writing to an array.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetArray()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $data = [$key1 => $val1, $key2 => $val2, $key3 => $val3];
        $container = $data;
        $subject = $this->createInstance(['_normalizeKey']);

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key2)
            ->will($this->returnValue($key2));

        $reflection = new ReflectionMethod($subject, '_containerUnset');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key2]);

        $this->assertEquals([$key1 => $val1, $key3 => $val3], $container, 'Modified state is wrong');
    }

    /**
     * Tests that `_containerUnset()` works as expected when writing to an `stcClass` object.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetStdClass()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $data = [$key1 => $val1, $key2 => $val2, $key3 => $val3];
        $container = (object) $data;
        $subject = $this->createInstance(['_normalizeKey']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key2)
            ->will($this->returnValue($key2));

        $reflection = new ReflectionMethod($subject, '_containerUnset');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key2]);
        $this->assertEquals((object) [$key1 => $val1, $key3 => $val3], $container, 'Modified state is wrong');
    }

    /**
     * Tests that `_containerUnset()` works as expected when writing to an `ArrayAccess` object.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetArrayAccess()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $data = [$key1 => $val1, $key2 => $val2, $key3 => $val3];
        $container = $this->createArrayAccess(['offsetUnset'], [$data]);
        $subject = $this->createInstance(['_normalizeKey']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key2)
            ->will($this->returnValue($key2));

        $container->expects($this->exactly(1))
            ->method('offsetUnset')
            ->with($key2);

        $reflection = new ReflectionMethod($subject, '_containerUnset');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key2]);
        // Container does not get modified because the method is not proxying to the original, and thus
        // the state of the original container is not modified. However, we can still know that everything is correct
        // because `offsetUnset()` is being invoked correctly. Testing of the result can be done in a functional test.
    }

    /**
     * Tests that `_containerUnset()` fails as expected when checking an `ArrayAccess` object.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetArrayAccessFailureOffsetExists()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $initialData = $data;
        $container = $this->createArrayAccess(['offsetExists'], [$initialData]);
        $innerException = $this->createException('Error in `offsetExists()`');
        $exception = $this->createContainerException('Could not check container');
        $subject = $this->createInstance(['_normalizeKey', '_createContainerException']);
        $_subject = $this->reflect($subject);

        $container->expects($this->exactly(1))
            ->method('offsetExists')
            ->with($key)
            ->will($this->throwException($innerException));

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key)
            ->will($this->returnValue($key));

        $subject->expects($this->exactly(1))
            ->method('_createContainerException')
            ->with(
                $this->matchesRegularExpression(sprintf('!%1$s!', $key)),
                null,
                $innerException,
                null
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('Psr\Container\ContainerExceptionInterface');
        $reflection = new ReflectionMethod($subject, '_containerUnset');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key]);
    }

    /**
     * Tests that `_containerUnset()` fails as expected when writing to an `ArrayAccess` object.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetArrayAccessFailureOffsetUnset()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $initialData = $data;
        $container = $this->createArrayAccess(['offsetUnset', 'offsetExists'], [$initialData]);
        $innerException = $this->createException('Error in `offsetUnset()`');
        $exception = $this->createContainerException('Could not write to container');
        $subject = $this->createInstance(['_normalizeKey', '_createContainerException']);
        $_subject = $this->reflect($subject);

        $container->expects($this->exactly(1))
            ->method('offsetExists')
            ->with($key)
            ->will($this->returnValue(isset($data[$key])));
        $container->expects($this->exactly(1))
            ->method('offsetUnset')
            ->with($key)
            ->will($this->throwException($innerException));

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key)
            ->will($this->returnValue($key));

        $subject->expects($this->exactly(1))
            ->method('_createContainerException')
            ->with(
                $this->matchesRegularExpression(sprintf('!%1$s!', $key)),
                null,
                $innerException,
                null
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('Psr\Container\ContainerExceptionInterface');
        $reflection = new ReflectionMethod($subject, '_containerUnset');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key]);
    }

    /**
     * Tests that `_containerUnset()` fails as expected when given an invalid container.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetFailureInvalidContainer()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $container = uniqid('container');
        $exception = $this->createInvalidArgumentException('Invalid container');
        $subject = $this->createInstance(['_normalizeKey', '_createInvalidArgumentException']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key)
            ->will($this->returnValue($key));

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
        $reflection = new ReflectionMethod($subject, '_containerUnset');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key]);
    }

    /**
     * Tests that `_containerUnset()` fails as expected when given an invalid key.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetFailureInvalidKey()
    {
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $container = $this->createArrayAccess(null, [$data]);
        $exception = $this->createOutOfRangeException('Invalid key');
        $subject = $this->createInstance(['_normalizeKey']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key)
            ->will($this->throwException($exception));

        $this->setExpectedException('OutOfRangeException');
        $reflection = new ReflectionMethod($subject, '_containerUnset');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $key]);
    }

    /**
     * Tests that `_containerUnset()` fails as expected when trying to unset an array key that doesn't exist.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetArrayFailureNotFound()
    {
        $randomKey = uniqid('random-key');
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $container = $data;
        $exception = $this->createNotFoundException('Key not found');
        $subject = $this->createInstance(['_normalizeKey', '_createNotFoundException']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($randomKey)
            ->will($this->returnValue($randomKey));
        $subject->expects($this->exactly(1))
            ->method('_createNotFoundException')
            ->with(
                $this->matchesRegularExpression(sprintf('!%1$s!', $randomKey)),
                null,
                null,
                null,
                $randomKey
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('Psr\Container\NotFoundExceptionInterface');
        $reflection = new ReflectionMethod($subject, '_containerUnset');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $randomKey]);
    }

    /**
     * Tests that `_containerUnset()` fails as expected when trying to unset an `stdClass` key that doesn't exist.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetStdClassFailureNotFound()
    {
        $randomKey = uniqid('random-key');
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $container = (object) $data;
        $exception = $this->createNotFoundException('Key not found');
        $subject = $this->createInstance(['_normalizeKey', '_createNotFoundException']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($randomKey)
            ->will($this->returnValue($randomKey));
        $subject->expects($this->exactly(1))
            ->method('_createNotFoundException')
            ->with(
                $this->matchesRegularExpression(sprintf('!%1$s!', $randomKey)),
                null,
                null,
                null,
                $randomKey
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('Psr\Container\NotFoundExceptionInterface');
        $reflection = new ReflectionMethod($subject, '_containerUnset');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $randomKey]);
    }

    /**
     * Tests that `_containerUnset()` fails as expected when trying to unset an `ArrayAccess` key that doesn't exist.
     *
     * @since [*next-version*]
     */
    public function testContainerUnsetArrayAccessFailureNotFound()
    {
        $randomKey = uniqid('random-key');
        $key = uniqid('key');
        $val = uniqid('val');
        $data = [$key => $val];
        $container = $this->createArrayAccess(['offsetExists'], [$data]);
        $exception = $this->createNotFoundException('Key not found');
        $subject = $this->createInstance(['_normalizeKey', '_createNotFoundException']);
        $_subject = $this->reflect($subject);

        $container->expects($this->exactly(1))
            ->method('offsetExists')
            ->with($randomKey)
            ->will($this->returnValue(isset($data[$randomKey])));

        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($randomKey)
            ->will($this->returnValue($randomKey));
        $subject->expects($this->exactly(1))
            ->method('_createNotFoundException')
            ->with(
                $this->matchesRegularExpression(sprintf('!%1$s!', $randomKey)),
                null,
                null,
                null,
                $randomKey
            )
            ->will($this->returnValue($exception));

        $this->setExpectedException('Psr\Container\NotFoundExceptionInterface');
        $reflection = new ReflectionMethod($subject, '_containerUnset');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $randomKey]);
    }
}
