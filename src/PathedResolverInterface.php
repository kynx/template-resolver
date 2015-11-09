<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

interface PathedResolverInterface extends ResolverInterface
{
    /**
     * Add a (optionally namespaced) template path to the resolver
     * @param string $path
     * @param string|null $namespace
     * @return $this
     */
    public function addTemplatePath($path, $namespace = null);
}
