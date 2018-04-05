<?php

namespace Dhii\Data\Container\UnitTest;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionMethod;
use Xpmock\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Exception as RootException;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ContainerSetPathCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Data\Container\ContainerSetPathCapableTrait';

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
     * Creates a new `ContainerInterface` instance.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject|ContainerInterface
     */
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
     * Tests that `_containerSetPath()` works as expected.
     *
     * @since [*next-version*]
     */
    public function testContainerSetPathSuccessfully()
    {
        $key1 = uniqid('key1');
        $key2 = uniqid('key2');
        $val = uniqid('val');
        $newVal = uniqid('new-val');
        $path = [$key1, $key1];
        $data = [
            $key1 => [
                $key2 => $val,
            ],
        ];
        $container = (object) $data;
        $subject = $this->createInstance(['_normalizeArray', '_containerSet']);

        $subject->expects($this->exactly(count($path)))
            ->method('_normalizeArray')
            ->will($this->returnArgument(0));

        $subject->expects($this->exactly(1))
            ->method('_containerSet');

        $reflection = new ReflectionMethod($subject, '_containerSetPath');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $path, $newVal]);
    }

    /**
     * Tests that `_containerSetPath()` will throw exception when path is empty.
     *
     * @since [*next-version*]
     */
    public function testContainerSetPathThrowsWhenPathIsEmpty()
    {
        $key1 = uniqid('key1');
        $key2 = uniqid('key2');
        $val = uniqid('val');
        $newVal = uniqid('new-val');
        $path = [];
        $pathException = $this->createInvalidArgumentException('Path is empty');
        $data = [
            $key1 => [
                $key2 => $val,
            ],
        ];
        $container = (object) $data;
        $subject = $this->createInstance(['_normalizeArray']);

        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->will($this->returnArgument(0));

        $subject->expects($this->exactly(1))
            ->method('_createInvalidArgumentException')
            ->will($this->throwException($pathException));

        $this->setExpectedException('InvalidArgumentException');

        $reflection = new ReflectionMethod($subject, '_containerSetPath');
        $reflection->setAccessible(true);
        $reflection->invokeArgs($subject, [&$container, $path, $newVal]);
    }
}
