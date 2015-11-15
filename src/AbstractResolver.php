<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

abstract class AbstractResolver implements ResolverInterface
{
    const DEFAULT_NAMESPACE = '__DEFAULT__';

    private $isCompiled = false;

    public function isCompiled()
    {
        return $this->isCompiled;
    }

    public function setIsCompiled($isCompiled)
    {
        $this->isCompiled = $isCompiled;
        return $this;
    }

    protected function parseTemplateNamespace($template)
    {
        if (preg_match('#^(?P<namespace>[^:]+)::(?P<template>.*)$#', $template, $matches)) {
            return $matches;
        }
        return ['namespace' => self::DEFAULT_NAMESPACE, 'template' => $template];
    }
}
