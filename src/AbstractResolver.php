<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

abstract class AbstractResolver implements ResolverInterface
{
    const DEFAULT_NAMESPACE = '__DEFAULT__';

    private $extension = 'template';

    private $separator = '/';

    private $isCompiled = false;

    protected $paths = [];


    public function getExtension()
    {
        return $this->extension;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

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
