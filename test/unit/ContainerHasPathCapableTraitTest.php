<?php

namespace Dhii\Data\Container\UnitTest;

use InvalidArgumentException;
use Xpmock\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Exception as RootException;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ContainerHasPathCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Data\Container\ContainerHasPathCapableTrait';

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
     * Tests that `_containerHasPath()` works correctly on true check.
     *
     * @since [*next-version*]
     */
    public function testContainerHasPathTrueCheck()
    {
        $container = uniqid('container');
        $path = [
            uniqid('p1'),
            uniqid('p2'),
            uniqid('p3'),
            uniqid('p4'),
            uniqid('p5'),
        ];
        $pathLength = count($path);
        $subject = $this->createInstance(['_normalizeArray', '_containerGet', '_containerHas']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->will($this->returnArgument(0));

        $subject->expects($this->exactly($pathLength - 1))
            ->method('_containerGet')
            ->will($this->returnArgument(0));

        $subject->expects($this->exactly($pathLength))
            ->method('_containerHas')
            ->will($this->returnValue(true));

        $result = $_subject->_containerHasPath($container, $path);
        $this->assertEquals(true, $result, 'Wrong has check returned');
    }

    /**
     * Tests that `_containerHasPath()` works correctly on false check.
     *
     * @since [*next-version*]
     */
    public function testContainerHasPathFalseCheck()
    {
        $container = uniqid('container');
        $p1 = uniqid('p1');
        $p2 = uniqid('p2');
        $path = [
            $p1,
            $p2,
        ];
        $pathLength = count($path);
        $subject = $this->createInstance(['_normalizeArray', '_containerGet', '_containerHas']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->will($this->returnArgument(0));

        $subject->expects($this->exactly($pathLength - 1))
            ->method('_containerGet')
            ->will($this->returnArgument(0));

        $subject->expects($this->exactly($pathLength))
            ->method('_containerHas')
            ->will($this->returnValueMap([
                [$container, $p1, true],
                [$container, $p2, false],
            ]));

        $result = $_subject->_containerHasPath($container, $path);
        $this->assertEquals(false, $result, 'Wrong has check returned');
    }

    /**
     * Tests that `_containerHasPath()` fails when wrong argument passed.
     *
     * @since [*next-version*]
     */
    public function testContainerHasPathFails()
    {
        $container = uniqid('container');
        $subject = $this->createInstance(['_normalizeArray', '_createInvalidArgumentException']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeArray')
            ->will($this->returnArgument(0));

        $subject->expects($this->exactly(1))
            ->method('_createInvalidArgumentException')
            ->will($this->throwException($this->createInvalidArgumentException('Not a valid path')));

        $this->setExpectedException('InvalidArgumentException');
        $_subject->_containerHasPath($container, []);
    }
}
