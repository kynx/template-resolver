<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

use Psr\Cache\CacheItemPoolInterface;

final class CacheResolver extends AbstractResolver implements SavingResolverInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * @param CacheItemPoolInterface $cacheItemPool
     */
    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @param $template
     * @return Result|null
     */
    public function resolve($template, callable $next = null)
    {
        $resolvedTemplate = $this->parseTemplateNamespace($template);
        $namespace = $resolvedTemplate['namespace'];
        $template  = $resolvedTemplate['template'];

        $result = $this->fetchResultForNamespace($template, $namespace);
        if ($result || $namespace === self::DEFAULT_NAMESPACE) {
            return $result;
        }

        return $this->fetchResultForNamespace($template, self::DEFAULT_NAMESPACE);
    }

    public function save($name, $contents)
    {
        $resolvedTemplate = $this->parseTemplateNamespace($name);
        $key = $resolvedTemplate['namespace'] . '::' . $resolvedTemplate['template'];
        $cacheItem = $this->cacheItemPool->getItem($key);
        $cacheItem->set($contents);
        return $this->cacheItemPool->save($cacheItem);
    }

    /**
     * Attempt to retrieve a result for a given namespace.
     *
     * @param $template
     * @param $namespace
     * @return Result|null
     */
    private function fetchResultForNamespace($template, $namespace)
    {
        $key = $namespace . '::' . $template;
        $cacheItem = $this->cacheItemPool->getItem($key);
        if ($cacheItem->isHit()) {
            return new Result($key, $cacheItem->get(), true, $this->isCompiled());
        }
        return null;
    }
}
