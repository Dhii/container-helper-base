<?php

namespace Dhii\Data\Container\UnitTest;

use ArrayObject;
use Dhii\Data\Container\ContainerHasCapableTrait as TestSubject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ContainerHasCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Data\Container\ContainerHasCapableTrait';

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
     * @return MockObject|NotFoundExceptionInterface The new exception.
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
     * Tests that `_containerGet()` works as expected when given an `ArrayAccess` which throws in `offsetExists()`.
     *
     * @since [*next-version*]
     */
    public function testContainerGetArrayAccessFailureOffsetExists()
    {
        $key = uniqid('key');
        $containerException = $this->createContainerException('Error checking for key');
        $exception = $this->createException('Problem inside `offsetExists()`');
        $container = $this->createArrayAccess(['offsetExists']);
        $subject = $this->createInstance(['_createContainerException', '_normalizeString']);
        $_subject = $this->reflect($subject);

        $container->expects($this->exactly(1))
            ->method('offsetExists')
            ->with($key)
            ->will($this->throwException($exception));
        $subject->expects($this->exactly(1))
            ->method('_normalizeKey')
            ->with($key)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_createContainerException')
            ->with(
                $this->isType('string'),
                null,
                $exception,
                null
            )
            ->will($this->returnValue($containerException));

        $this->setExpectedException('Psr\Container\ContainerExceptionInterface');
        $_subject->_containerHas($container, $key);
    }
}
