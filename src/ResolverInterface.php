<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

interface ResolverInterface
{
    /**
     * Marks resolver as returning compiled templates
     *
     * If set, the resolver must return results that where Result::isCompiled() is true
     *
     * @param boolean $isCompiled
     * @return $this
     */
    public function setIsCompiled($isCompiled);

    /**
     * Returns resource matching (optionally namespaced) given name, or null if it can't be resolved
     *
     * @param string $template
     * @return Result|null
     */
    public function resolve($template);
}
