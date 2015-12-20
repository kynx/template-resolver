<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace KynxTest\Template\Resolver;

use Kynx\Template\Resolver\AbstractResolver;
use Kynx\Template\Resolver\AggregateResolver;
use Kynx\Template\Resolver\CacheResolver;
use Kynx\Template\Resolver\FilesystemResolver;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

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

    public function testIteratorIsClone()
    {
        $resolver = $this->getFilesystemResolver();
        $this->resolver->attach($resolver);
        $this->assertEquals(1, count($this->resolver));
        foreach ($this->resolver as $resolver) {
            $this->assertInstanceOf(FilesystemResolver::class, $resolver);
        }
        $this->assertEquals(1, count($this->resolver));
    }

    public function testUnresolvedReturnsNull()
    {
        $cache = $this->getCacheResolver(AbstractResolver::DEFAULT_NAMESPACE . '::missing', '', false);
        $filesystem = $this->getFilesystemResolver();
        $filesystem->addPath(__DIR__ . '/templates');
        $this->resolver->attach($filesystem, 0);
        $this->resolver->attach($cache);
        $result = $this->resolver->resolve('missing');
        $this->assertNull($result);
    }

    public function testCacheTakesPriority()
    {
        $cache = $this->getCacheResolver(AbstractResolver::DEFAULT_NAMESPACE . '::test', 'cached');
        $filesystem = $this->getFilesystemResolver();
        $filesystem->addPath(__DIR__ . '/templates');
        $this->resolver->attach($filesystem, 0);
        $this->resolver->attach($cache);
        $result = $this->resolver->resolve('test');
        $this->assertEquals('cached', (string) $result);
    }

    public function testFilesystemResolvesWhenCacheMissing()
    {
        $cache = $this->getCacheResolver(AbstractResolver::DEFAULT_NAMESPACE . '::test', '', false);
        $filesystem = $this->getFilesystemResolver();
        $filesystem->addPath(__DIR__ . '/templates');
        $this->resolver->attach($filesystem, 0);
        $this->resolver->attach($cache);
        $result = $this->resolver->resolve('test');
        $this->assertEquals("test\n", (string) $result);
    }

    public function testAddPath()
    {
        $filesystem = $this->getFilesystemResolver();
        $this->resolver->attach($filesystem, 0);
        $this->resolver->addPath(__DIR__ . '/templates');
        $result = $this->resolver->resolve('test');
        $this->assertEquals("test\n", (string) $result);
    }

    /**
     * @expectedException \Kynx\Template\Resolver\Exception\ResolverTypeNotFoundException
     */
    public function testAddPathNotSupported()
    {
        $cache = $this->getCacheResolver(AbstractResolver::DEFAULT_NAMESPACE . '::test', '', false);
        $this->resolver->attach($cache);
        $this->resolver->addPath(__DIR__ . '/templates');
    }

    public function testGetPaths()
    {
        $cache = $this->getCacheResolver(AbstractResolver::DEFAULT_NAMESPACE . '::test', '', false);
        $filesystem = $this->getFilesystemResolver();
        $path1 = __DIR__ . '/templates';
        $path2 = __DIR__ . '/templates/test1';
        $filesystem->addPath($path1)
            ->addPath($path2, 'test');
        $this->resolver->attach($filesystem, 0);
        $this->resolver->attach($cache);
        $paths = $this->resolver->getPaths();
        $this->assertEquals(2, count($paths));
        $this->assertEquals(new \SplStack($path1), $paths['__DEFAULT__']);
        $this->assertEquals(new \SplStack($path2), $paths['test']);
    }

    /**
     * @expectedException \Kynx\Template\Resolver\Exception\ResolverTypeNotFoundException
     */
    public function testGetPathsNotSupported()
    {
        $cache = $this->getCacheResolver(AbstractResolver::DEFAULT_NAMESPACE . '::test', '', false);
        $this->resolver->attach($cache);
        $this->resolver->getPaths();
    }

    public function testSave()
    {
        $cache = $this->getCacheResolver(AbstractResolver::DEFAULT_NAMESPACE . '::test', 'foo', true);
        $this->resolver->attach($cache);
        $this->resolver->save('test', 'foo');
        $result = $this->resolver->resolve('test');
        $this->assertEquals('foo', (string) $result);
    }

    /**
     * @expectedException \Kynx\Template\Resolver\Exception\ResolverTypeNotFoundException
     */
    public function testSaveNotSupported()
    {
        $filesystem = $this->getFilesystemResolver();
        $this->resolver->attach($filesystem);
        $this->resolver->save('test', 'foo');
    }

    /**
     * @expectedException \Kynx\Template\Resolver\Exception\BadMethodCallException
     */
    public function testSetIsCompiled()
    {
        $this->resolver->setIsCompiled(true);
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
