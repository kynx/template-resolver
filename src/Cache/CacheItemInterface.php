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
interface CacheItemInterface
{
    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     *
     * The value returned must be identical to the value originally stored by set().
     *
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get();

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit. False otherwise.
     */
    public function isHit();
}
