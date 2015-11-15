<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace KynxTest\Template\Resolver;

use Kynx\Template\Resolver\FilesystemResolver;
use PHPUnit_Framework_TestCase as TestCase;

final class FilesystemResolverTest extends TestCase
{
    /**
     * @var FileSystemResolver
     */
    protected $resolver;

    public function setUp()
    {
        $this->resolver = new FilesystemResolver();
        $this->resolver->setExtension('template')
            ->setSeparator('/');
    }

    public function testDefaultNamespaceTemplateResolves()
    {
        $this->resolver->addPath(__DIR__ . '/templates');
        $result = $this->resolver->resolve('test');
        $this->assertEquals("test\n", (string) $result);
        $this->assertEquals('__DEFAULT__::test.template', $result->getKey());
        $this->assertFalse($result->isCompiled());
    }

    public function testDefaultNamespaceMissingIsNull()
    {
        $this->resolver->addPath(__DIR__ . '/templates');
        $result = $this->resolver->resolve('missing');
        $this->assertNull($result);
    }

    public function testDefaultNamespaceWrongExtension()
    {

        $this->resolver->addPath(__DIR__ . '/templates')
            ->setExtension('template2');
        $result = $this->resolver->resolve('test');
        $this->assertNull($result);
    }

    /**
     * @expectedException \Kynx\Template\Resolver\Exception\InvalidPathException
     */
    public function testAddInvalidPath()
    {
        $this->resolver->addPath(__DIR__ . '/templates1');
    }

    public function testDefaultNamespaceWithPath()
    {
        $this->resolver->addPath(__DIR__ . '/templates');
        $result = $this->resolver->resolve('test1/test');
        $this->assertEquals("test1 template\n", (string) $result);
    }

    public function testDefaultNamespaceWithAlternateSeparator()
    {
        $this->resolver->addPath(__DIR__ . '/templates')
            ->setSeparator('.');
        $result = $this->resolver->resolve('test1.test');
        $this->assertEquals("test1 template\n", (string) $result);
    }

    public function testNamespacedTemplate()
    {
        $this->resolver->addPath(__DIR__ . '/templates')
            ->addPath(__DIR__ . '/templates/test1', 'test');
        $result = $this->resolver->resolve('test::test');
        $this->assertEquals("test1 template\n", (string) $result);
        $this->assertEquals('test::test.template', $result->getKey());
    }

    public function testNamespacedDefaultResolved()
    {
        $this->resolver->addPath(__DIR__ . '/templates')
            ->addPath(__DIR__ . '/templates/test1', 'test');
        $result = $this->resolver->resolve('test');
        $this->assertEquals("test\n", (string) $result);
    }

    public function testDefaultsToDefaultNamespace()
    {
        $this->resolver->addPath(__DIR__ . '/templates')
            ->addPath(__DIR__ . '/templates/test1', 'test');
        $result = $this->resolver->resolve('test::test2');
        $this->assertEquals("test2\n", (string) $result);
    }

    /**
     * @expectedException \Kynx\Template\Resolver\Exception\InvalidNamespaceException
     */
    public function testAddInvalidNamespace()
    {
        $this->resolver->addPath(__DIR__ . '/templates/test1', []);
    }

    public function testIsCompiled()
    {
        $this->resolver->addPath(__DIR__ . '/templates')
            ->setIsCompiled(true);
        $result = $this->resolver->resolve('test');
        $this->assertTrue($result->isCompiled());
    }

    public function testGetPaths()
    {
        $path1 = __DIR__ . '/templates';
        $path2 = __DIR__ . '/templates/test1';
        $this->resolver->addPath($path1)
            ->addPath($path2, 'test');
        $paths = $this->resolver->getPaths();
        $this->assertEquals(2, count($paths));
        $this->assertEquals(new \SplStack($path1), $paths['__DEFAULT__']);
        $this->assertEquals(new \SplStack($path2), $paths['test']);
    }
}
