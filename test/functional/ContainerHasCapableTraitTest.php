<?php

namespace Dhii\Data\Container\FuncTest;

use ArrayAccess;
use ArrayObject;
use Dhii\Data\Container\ContainerGetCapableTrait as TestSubject;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Container\ContainerInterface;
use stdClass;
use Xpmock\TestCase;

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
        $methods = $this->mergeValues(
            $methods,
            [
                '__',
                '_normalizeString',
                '_normalizeContainer',
                '_createInvalidArgumentException',
                '_createContainerException',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_normalizeString')
            ->will($this->returnCallback(function ($subject) {
                return (string) $subject;
            }));
        $mock->method('_normalizeContainer')
            ->will($this->returnCallback(function ($subject) {
                if (!($subject instanceof ContainerInterface) &&
                    !($subject instanceof ArrayAccess) &&
                    !($subject instanceof stdClass) &&
                    !is_array($subject)
                ) {
                    throw new InvalidArgumentException('Invalid container');
                }

                return $subject;
            }));
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function ($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );
        $mock->method('_createContainerException')->willReturnCallback(
            function ($m, $c, $p) {
                return $this->mockClassAndInterfaces('Exception', ['Psr\Container\ContainerExceptionInterface']);
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
     * Tests the `_containerHas()` method with a container to assert whether `true` is returned when the container has
     * the given key.
     *
     * @since [*next-version*]
     */
    public function testContainerHasContainer()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key-');
        $expected = true;

        // Mock container instance
        $container = $this->getMockBuilder('Psr\Container\ContainerInterface')
                          ->setMethods(['get', 'has'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('has')
                  ->with($key)
                  ->willReturn($expected);

        $actual = $reflect->_containerHas($container, $key);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerHas()` method with a container to assert whether `false` is returned when the container does
     * not have the given key.
     *
     * @since [*next-version*]
     */
    public function testContainerHasContainerNotFound()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key-');
        $expected = false;

        // Mock container instance
        $container = $this->getMockBuilder('Psr\Container\ContainerInterface')
                          ->setMethods(['get', 'has'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('has')
                  ->with($key)
                  ->willReturn($expected);

        $actual = $reflect->_containerHas($container, $key);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerHas()` method with a container to assert whether an exception is thrown when the container
     * throws an exception.
     *
     * @since [*next-version*]
     */
    public function testContainerHasContainerException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key-');
        $expected = false;

        // Mock container instance
        $containerException = $this->mockClassAndInterfaces('Exception', ['Psr\Container\ContainerExceptionInterface']);
        $container = $this->getMockBuilder('Psr\Container\ContainerInterface')
                          ->setMethods(['get', 'has'])
                          ->getMockForAbstractClass();
        $container->expects($this->once())
                  ->method('has')
                  ->with($key)
                  ->willThrowException($containerException);

        $this->setExpectedException('Psr\Container\ContainerExceptionInterface');

        $reflect->_containerHas($container, $key);
    }

    /**
     * Tests the `_containerHas()` method with an object to assert whether `true` is returned when the object has a
     * property that corresponds to the given key.
     *
     * @since [*next-version*]
     */
    public function testContainerHasObject()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key_');
        $expected = true;

        $container = new stdClass();
        $container->{$key} = rand(0, 100);

        $actual = $reflect->_containerHas($container, $key);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerHas()` method with an object to assert whether null values are deemed as existing.
     *
     * @since [*next-version*]
     */
    public function testContainerHasObjectNull()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key_');
        $expected = true;

        $container = new stdClass();
        $container->{$key} = null;

        $actual = $reflect->_containerHas($container, $key);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerHas()` method with an object to assert whether `false` is returned when the object does not
     * have a property that corresponds to the given key.
     *
     * @since [*next-version*]
     */
    public function testContainerHasObjectNotFound()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $wrongKey = uniqid('key_');
        $realKey = uniqid('key_');
        $expected = false;

        $container = new stdClass();
        $container->{$realKey} = rand(0, 100);

        $actual = $reflect->_containerHas($container, $wrongKey);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerHas()` method with an array to assert whether `true` is returned when the array has the
     * given key.
     *
     * @since [*next-version*]
     */
    public function testContainerHasArray()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key-');
        $expected = true;

        $container = [];
        $container[$key] = $expected;

        $actual = $reflect->_containerHas($container, $key);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests that `_containerHas()` works correctly when using integers to check for numeric string keys in `stdClass` objects`.
     *
     * @since [*next-version*]
     */
    public function testContainerHasIntObject()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $val = uniqid('expected-');
        $container = new stdClass();
        $container->{'19'} = $val;

        $result = $reflect->_containerHas($container, 19);

        $this->assertTrue($result, 'Subject failed to detect key');
    }

    /**
     * Tests that `_containerGet()` works correctly when using integers to check for numeric string keys in arrays.
     *
     * @since [*next-version*]
     */
    public function testContainerHasIntArray()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $val = uniqid('expected-');
        $container = ['19' => $val];

        $result = $reflect->_containerHas($container, 19);

        $this->assertTrue($result, 'Subject failed to detect key');
    }

    /**
     * Tests the `_containerHas()` method with an array to assert whether `false` is returned when the array does not
     * have the given key.
     *
     * @since [*next-version*]
     */
    public function testContainerHasArrayNotFound()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $wrongKey = uniqid('key-');
        $realKey = uniqid('key-');
        $expected = false;

        $container = [];
        $container[$realKey] = $expected;

        $actual = $reflect->_containerHas($container, $wrongKey);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerHas()` method with array access object to assert whether `true` is returned when the given
     * key is found.
     *
     * @since [*next-version*]
     */
    public function testContainerHasArrayAccess()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $key = uniqid('key-');
        $expected = true;
        $container = new ArrayObject([$key => $expected]);

        $actual = $reflect->_containerHas($container, $key);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerHas()` method with array access object to assert whether `false` is returned when the given
     * key is not found.
     *
     * @since [*next-version*]
     */
    public function testContainerHasArrayAccessNotFound()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $wrongKey = uniqid('key-');
        $realKey = uniqid('key-');
        $expected = false;
        $container = new ArrayObject([$realKey => $expected]);

        $actual = $reflect->_containerHas($container, $wrongKey);

        $this->assertEquals($expected, $actual, 'Expected and retrieved values do not match.');
    }

    /**
     * Tests the `_containerHas()` method with an invalid argument to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testContainerHasInvalidArgument()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_containerHas(uniqid('scalar-'), uniqid('key-'));
    }

    /**
     * Tests the `_containerHas()` method with a null argument to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testContainerHasNullArgument()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_containerHas(null, uniqid('key-'));
    }
}
