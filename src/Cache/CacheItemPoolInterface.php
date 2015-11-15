<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver\Cache;

/**
 * Trimmed down PSR-6 interface
 *
 * This is mostly here for code completion. Once PSR-6 is accepted we will use type declarations to enforce the full
 * interface, but for the time being these are the only parts of the proposed interface we actually use.
 *
 * @todo Switch to using real PSR interface when it becomes available
 * @link https://github.com/php-fig/fig-standards/blob/master/proposed/cache.md
 */
interface CacheItemPoolInterface
{
    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem($key);

    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(CacheItemInterface $item);
}
