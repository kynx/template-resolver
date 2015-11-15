<?php
/**
 * @author matt
 * @copyright 2015 Claritum Limited
 * @license Commercial
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
