<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace KynxTest\Template\Resolver;

use Kynx\Template\Resolver\AbstractResolver;
use Kynx\Template\Resolver\CacheResolver;
use Kynx\Template\Resolver\Cache\CacheItemPoolInterface;
use Kynx\Template\Resolver\Cache\CacheItemInterface;;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;

final class CacheResolverTest extends TestCase
{
    public function testDefaultNamespaceTemplateResolves()
    {
        $cacheKey = AbstractResolver::DEFAULT_NAMESPACE . '::test';
        $template = "test";
        $resolver = $this->getResolver($cacheKey, $template);
        $result = $resolver->resolve('test');
        $this->assertEquals($template, (string) $result);
        $this->assertEquals($cacheKey, $result->getKey());
        $this->assertFalse($result->isCompiled());
    }

    public function testDefaultNamespaceMissingIsNull()
    {
        $resolver = $this->getResolver('', '', false);
        $result = $resolver->resolve('missing');
        $this->assertNull($result);
    }

    /**
     * @expectedException \Kynx\Template\Resolver\Exception\InvalidPathException
     */
    public function testAddInvalidPath()
    {
        $this->resolver->addTemplatePath(__DIR__ . '/templates1');
    }

    public function testDefaultNamespaceWithPath()
    {
        $this->resolver->addTemplatePath(__DIR__ . '/templates');
        $result = $this->resolver->resolve('test1/test');
        $this->assertEquals("test1 template\n", (string) $result);
    }

    public function testDefaultNamespaceWithAlternateSeparator()
    {
        $this->resolver->addTemplatePath(__DIR__ . '/templates')
            ->setSeparator('.');
        $result = $this->resolver->resolve('test1.test');
        $this->assertEquals("test1 template\n", (string) $result);
    }

    public function testNamespacedTemplate()
    {
        $this->resolver->addTemplatePath(__DIR__ . '/templates')
            ->addTemplatePath(__DIR__ . '/templates/test1', 'test');
        $result = $this->resolver->resolve('test::test');
        $this->assertEquals("test1 template\n", (string) $result);
        $this->assertEquals(__DIR__ . '/templates/test1/test.template', $result->getKey());
    }

    public function testNamespacedDefaultResolved()
    {
        $this->resolver->addTemplatePath(__DIR__ . '/templates')
            ->addTemplatePath(__DIR__ . '/templates/test1', 'test');
        $result = $this->resolver->resolve('test');
        $this->assertEquals("test\n", (string) $result);
    }

    public function testDefaultsToDefaultNamespace()
    {
        $this->resolver->addTemplatePath(__DIR__ . '/templates')
            ->addTemplatePath(__DIR__ . '/templates/test1', 'test');
        $result = $this->resolver->resolve('test::test2');
        $this->assertEquals("test2\n", (string) $result);
    }

    /**
     * @expectedException \Kynx\Template\Resolver\Exception\InvalidNamespaceException
     */
    public function testAddInvalidNamespace()
    {
        $this->resolver->addTemplatePath(__DIR__ . '/templates/test1', []);
    }

    public function testIsCompiled()
    {
        $this->resolver->addTemplatePath(__DIR__ . '/templates')
            ->setIsCompiled(true);
        $result = $this->resolver->resolve('test');
        $this->assertTrue($result->isCompiled());
    }

    private function getResolver($cacheKey, $template, $isHit = true)
    {
        $itemProphesy = $this->prophesize(CacheItemInterface::class);
        $itemProphesy->isHit()
            ->willReturn($isHit);
        $itemProphesy->get()
            ->willReturn($isHit ? $template : null);
        $cacheProphesy = $this->prophesize(CacheItemPoolInterface::class);
        $cacheProphesy->getItem($cacheKey)
            ->willReturn($itemProphesy->reveal());
        return new CacheResolver($cacheProphesy->reveal());
    }
}
