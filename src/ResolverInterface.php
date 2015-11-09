<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

interface ResolverInterface
{
    /**
     * Returns resource matching (optionally namespaced) given name, or null if it can't be resolved
     * @param $name
     * @return Result|null
     */
    public function resolve($name);
}
