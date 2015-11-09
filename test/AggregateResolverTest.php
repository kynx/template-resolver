<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace KynxTest\Template\Resolver;

use Kynx\Template\Resolver\AbstractResolver;
use Kynx\Template\Resolver\AggregateResolver;
use Kynx\Template\Resolver\Cache\CacheItemInterface;
use Kynx\Template\Resolver\Cache\CacheItemPoolInterface;
use Kynx\Template\Resolver\CacheResolver;
use Kynx\Template\Resolver\FilesystemResolver;
use Kynx\Template\Resolver\Exception\InvalidNamespaceException;
use PHPUnit_Framework_TestCase as TestCase;

final class AggregateResolverTest extends TestCase
{
    /**
     * @var AggregateResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->resolver = new AggregateResolver();
    }

    public function testFetchByTypeReturnsInstance()
    {
        $resolver = $this->getFilesystemResolver();
        $this->resolver->attach($resolver);
        $this->assertEquals($resolver, $this->resolver->fetchByType(FilesystemResolver::class));
    }

    public function testFetchByTypeReturnsAggregate()
    {
        $resolver1 = $this->getFilesystemResolver();
        $resolver2 = $this->getFilesystemResolver();
        $this->resolver->attach($resolver1, 0);
        $this->resolver->attach($resolver2, 1);
        $aggregate =$this->resolver->fetchByType(FilesystemResolver::class);
        $this->assertInstanceOf(AggregateResolver::class, $aggregate);
    }

    /**
     * @expectedException \Kynx\Template\Resolver\Exception\ResolverTypeNotFoundException
     */
    public function testFetchByTypeThrowsNotFound()
    {
        $resolver = $this->getFilesystemResolver();
        $this->resolver->attach($resolver);
        $this->resolver->fetchByType(CacheResolver::class);
    }

    public function testCount()
    {
        $resolver = $this->getFilesystemResolver();
        $this->resolver->attach($resolver);
        $this->assertEquals(1, $this->resolver->count());
    }

    public function testGetIterator()
    {
        $resolver = $this->getFilesystemResolver();
        $this->resolver->attach($resolver);
        $iterator = $this->resolver->getIterator();
        $this->assertInstanceOf('SplPriorityQueue', $iterator);
        $this->assertEquals(1, $iterator->count());
        $this->assertEquals($resolver, $iterator->top());
    }

    public function testUnresolvedReturnsNull()
    {
        $cache = $this->getCacheResolver(AbstractResolver::DEFAULT_NAMESPACE . '::missing', '', false);
        $filesystem = $this->getFilesystemResolver();
        $filesystem->addTemplatePath(__DIR__ . '/templates');
        $this->resolver->attach($filesystem, 0);
        $this->resolver->attach($cache);
        $result = $this->resolver->resolve('missing');
        $this->assertNull($result);
    }

    public function testCacheTakesPriority()
    {
        $cache = $this->getCacheResolver(AbstractResolver::DEFAULT_NAMESPACE . '::test', 'cached');
        $filesystem = $this->getFilesystemResolver();
        $filesystem->addTemplatePath(__DIR__ . '/templates');
        $this->resolver->attach($filesystem, 0);
        $this->resolver->attach($cache);
        $result = $this->resolver->resolve('test');
        $this->assertEquals('cached', (string) $result);
    }

    public function testFilesystemResolvesWhenCacheMissing()
    {
        $cache = $this->getCacheResolver(AbstractResolver::DEFAULT_NAMESPACE . '::test', '', false);
        $filesystem = $this->getFilesystemResolver();
        $filesystem->addTemplatePath(__DIR__ . '/templates');
        $this->resolver->attach($filesystem, 0);
        $this->resolver->attach($cache);
        $result = $this->resolver->resolve('test');
        $this->assertEquals("test\n", (string) $result);
    }

    private function getFilesystemResolver()
    {
        return new FilesystemResolver();
    }

    private function getCacheResolver($cacheKey, $template, $isHit = true)
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
