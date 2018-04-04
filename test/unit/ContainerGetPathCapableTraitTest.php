<?php

namespace Dhii\Data\Container\UnitTest;

use Xpmock\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ContainerGetPathCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Data\Container\ContainerGetPathCapableTrait';

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

    public function createContainer($methods = [])
    {
        $mock = $this->getMockBuilder('Psr\Container\ContainerInterface')
            ->setMethods($methods)
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return Exception The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

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
     * Tests that `_containerGetPath()` works correctly.
     *
     * @since [*next-version*]
     */
    public function testContainerGetPath()
    {
        $key1 = uniqid('key');
        $key2 = uniqid('key');
        $value = uniqid('value');
        $container1 = uniqid('container');
        $container2 = uniqid('container');
        $subject = $this->createInstance(['_containerGet', '_normalizeIterable']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(2))
            ->method('_containerGet')
            ->will($this->returnValueMap([
                [$container1, $key1, $container2],
                [$container2, $key2, $value],
            ]));

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->will($this->returnArgument(0));

        $result = $_subject->_containerGetPath($container1, [$key1, $key2]);
        $this->assertEquals($value, $result, 'Wrong result returned');
    }

    /**
     * Tests that `_containerGetPath()` will sent internal exceptions up to caller.
     *
     * @since [*next-version*]
     */
    public function testContainerGetPathFailure()
    {
        $key1 = uniqid('key');
        $key2 = uniqid('key');
        $container1 = uniqid('container');
        $subject = $this->createInstance(['_containerGet', '_normalizeIterable']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->will($this->returnArgument(0));

        $subject->expects($this->exactly(1))
            ->method('_containerGet')
            ->will($this->throwException(
                $this->createException('Problem inside `_containerGet()`')
            ));

        $this->setExpectedException('Exception');
        $_subject->_containerGetPath($container1, [$key1, $key2]);
    }
}
