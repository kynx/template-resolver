<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace KynxTest\Template\Resolver;

use Kynx\Template\Resolver\AbstractResolver;
use Kynx\Template\Resolver\CacheResolver;
use Kynx\Template\Resolver\Cache\CacheItemPoolInterface;
use Kynx\Template\Resolver\Cache\CacheItemInterface;
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
        $cacheKey = AbstractResolver::DEFAULT_NAMESPACE . '::missing';
        $resolver = $this->getResolver($cacheKey, '', false);
        $result = $resolver->resolve('missing');
        $this->assertNull($result);
    }

    public function testDefaultNamespaceWithPath()
    {
        $cacheKey = AbstractResolver::DEFAULT_NAMESPACE . '::test1/test';
        $template = "test1 template";
        $resolver = $this->getResolver($cacheKey, $template);
        $result = $resolver->resolve('test1/test');
        $this->assertEquals("test1 template", (string) $result);
        $this->assertEquals($cacheKey, $result->getKey());
    }

    public function testNamespacedTemplate()
    {
        $cacheKey = 'test::test';
        $template = "test1 template";
        $resolver = $this->getResolver($cacheKey, $template);
        $result = $resolver->resolve('test::test');
        $this->assertEquals("test1 template", (string) $result);
        $this->assertEquals($cacheKey, $result->getKey());
    }

    public function testNamespacedDefaultResolved()
    {
        $cacheProphesy = $this->prophesize(CacheItemPoolInterface::class);
        $itemProphesy = $this->prophesize(CacheItemInterface::class);
        $cacheProphesy->getItem(Argument::type('string'))
            ->will(function ($args) use ($itemProphesy) {
                $isHit = $args[0] == AbstractResolver::DEFAULT_NAMESPACE . '::test';
                $itemProphesy->isHit()
                    ->willReturn($isHit);
                $itemProphesy->get()
                    ->willReturn($isHit ? "test\n" : null);
                return $itemProphesy->reveal();
            });
        $resolver = new CacheResolver($cacheProphesy->reveal());
        $result = $resolver->resolve('test::test');
        $this->assertNotNull($result);
        $this->assertEquals("test\n", (string) $result);
    }

    public function testIsCompiled()
    {
        $cacheKey = AbstractResolver::DEFAULT_NAMESPACE . '::test';
        $template = "test";
        $resolver = $this->getResolver($cacheKey, $template);
        $resolver->setIsCompiled(true);
        $result = $resolver->resolve('test');
        $this->assertTrue($result->isCompiled());
    }

    public function testSave()
    {
        $cacheKey = AbstractResolver::DEFAULT_NAMESPACE . '::test';
        $template = "test";
        $resolver = $this->getResolver($cacheKey, $template);
        $resolver->save($cacheKey, $template);
        $result = $resolver->resolve('test');
        $this->assertEquals($template, $result->getContents());
    }

    private function getResolver($cacheKey, $template, $isHit = true)
    {
        $itemProphesy = $this->prophesize(CacheItemInterface::class);
        $itemProphesy->isHit()
            ->willReturn($isHit);
        $itemProphesy->get()
            ->willReturn($isHit ? $template : null);
        $itemProphesy->set(Argument::any())
            ->willReturn($itemProphesy->reveal());
        $cacheProphesy = $this->prophesize(CacheItemPoolInterface::class);
        $cacheProphesy->getItem($cacheKey)
            ->willReturn($itemProphesy->reveal());
        $cacheProphesy->save(Argument::type(CacheItemInterface::class))
            ->willReturn(true);
        return new CacheResolver($cacheProphesy->reveal());
    }
}
