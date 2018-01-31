<?php

namespace Dhii\Data\Container\FuncTest;

use ArrayIterator;
use ArrayObject;
use Dhii\Data\Container\ContainerGetCapableTrait as TestSubject;
use InvalidArgumentException;
use IteratorIterator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ContainerGetCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Data\Container\ContainerGetCapableTrait';

    /**
     * The FQN of the not found exception interface.
     *
     * @since [*next-version*]
     */
    const NOT_FOUND_EXCEPTION_FQN = 'Psr\Container\NotFoundExceptionInterface';

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
        $methods = $this->mergeValues(
            $methods,
            [
                '__',
                '_normalizeString',
                '_createInvalidArgumentException',
                '_createNotFoundException',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_normalizeString')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );
        $mock->method('_createNotFoundException')->willReturnCallback(
            function($m, $c, $p) {
                return $this->mockClassAndInterfaces('Exception', [static::NOT_FOUND_EXCEPTION_FQN]);
            }
        );

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
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return object The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf(
            'abstract class %1$s extends %2$s implements %3$s {}',
            [
                $paddingClassName,
                $className,
                implode(', ', $interfaceNames),
            ]
        );
        eval($definition);

        return $this->getMockForAbstractClass($paddingClassName);
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
     * Tests the `_containerGet()` method with a container to assert whether the correct value is retrieved for the
     * given key.
     *
     * @since [*next-version*]
     */
    public function testContainerGetContainer()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key-');

        // Mock container instance
        $expected = uniqid('expected-');
        $container = $this->getMockBuilder('Psr\Container\ContainerInterface')
                          ->setMethods(['get', 'has'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with($key)
                  ->willReturn($expected);

        $actual = $reflect->_containerGet($container, $key);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerGet()` method with a container to assert whether an exception is thrown when the key is
     * not found.
     *
     * @since [*next-version*]
     */
    public function testContainerGetContainerNotFound()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key-');
        $notFoundException = $this->mockClassAndInterfaces('Exception', [static::NOT_FOUND_EXCEPTION_FQN]);

        // Mock container instance
        $container = $this->getMockBuilder('Psr\Container\ContainerInterface')
                          ->setMethods(['get', 'has'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('get')
                  ->with($key)
                  ->willThrowException($notFoundException);

        $this->setExpectedException(static::NOT_FOUND_EXCEPTION_FQN);

        $reflect->_containerGet($container, $key);
    }

    /**
     * Tests the `_containerGet()` method with an object to assert whether the correct value is retrieved for the
     * given key.
     *
     * @since [*next-version*]
     */
    public function testContainerGetObject()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key_');
        $expected = uniqid('expected-');

        $container = new stdClass();
        $container->{$key} = $expected;

        $actual = $reflect->_containerGet($container, $key);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerGet()` method with an object to assert whether an exception is thrown when the key is
     * not found.
     *
     * @since [*next-version*]
     */
    public function testContainerGetObjectNotFound()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $realKey = uniqid('key_');
        $wrongKey = uniqid('key_');
        $expected = uniqid('expected-');

        $container = new stdClass();
        $container->{$realKey} = $expected;

        $this->setExpectedException(static::NOT_FOUND_EXCEPTION_FQN);

        $reflect->_containerGet($container, $wrongKey);
    }

    /**
     * Tests the `_containerGet()` method with an array to assert whether the correct value is retrieved for the
     * given key.
     *
     * @since [*next-version*]
     */
    public function testContainerGetArray()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key-');
        $expected = uniqid('expected-');

        $container = [];
        $container[$key] = $expected;

        $actual = $reflect->_containerGet($container, $key);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerGet()` method with an array to assert whether an exception is thrown when the key is
     * not found.
     *
     * @since [*next-version*]
     */
    public function testContainerGetArrayNotFound()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $realKey = uniqid('key-');
        $wrongKey = uniqid('key-');
        $expected = uniqid('expected-');

        $container = [];
        $container[$realKey] = $expected;

        $this->setExpectedException(static::NOT_FOUND_EXCEPTION_FQN);

        $reflect->_containerGet($container, $wrongKey);
    }

    /**
     * Tests the `_containerGet()` method with array access object to assert whether the correct value is retrieved for
     * the given key.
     *
     * @since [*next-version*]
     */
    public function testContainerGetArrayAccess()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key-');
        $expected = uniqid('expected-');
        $container = new ArrayObject([$key => $expected]);

        $actual = $reflect->_containerGet($container, $key);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerGet()` method with an array access object to assert whether an exception is thrown when the
     * key is not found.
     *
     * @since [*next-version*]
     */
    public function testContainerGetArrayAccessNotFound()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $realKey = uniqid('key-');
        $wrongKey = uniqid('key-');
        $expected = uniqid('expected-');
        $container = new ArrayObject([$realKey => $expected]);

        $this->setExpectedException(static::NOT_FOUND_EXCEPTION_FQN);

        $reflect->_containerGet($container, $wrongKey);
    }

    /**
     * Tests the `_containerGet()` method with an invalid argument to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testContainerGetInvalidArgument()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_containerGet(uniqid('scalar-'), uniqid('key-'));
    }

    /**
     * Tests the `_containerGet()` method with a null argument to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testContainerGetNullArgument()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_containerGet(null, uniqid('key-'));
    }
}
