<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

interface SavingResolverInterface
{
    /**
     * Saves resource with (optionally namespaced) name
     *
     * @param string $name
     * @param mixed $value
     */
    public function save($name, $contents);
}
