<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

use SplStack;

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

    /**
     * Add a (optionally namespaced) template path to the resolver
     * @param string $path
     * @param string|null $namespace
     * @return $this
     */
    public function addTemplatePath($path, $namespace = null)
    {
        if (null !== $namespace && ! is_string($namespace)) {
            throw new Exception\InvalidNamespaceException('Namespace must be a string');
        }

        $namespace = $namespace ?: self::DEFAULT_NAMESPACE;
        $path = rtrim((string) $path, '/\\');
        $this->getTemplatePath($namespace)->push($path);

        return $this;
    }

    /**
     * @param $namespace
     * @return SplStack
     */
    protected function getTemplatePath($namespace)
    {
        if (!isset($this->paths[$namespace])) {
            $this->paths[$namespace] = new SplStack();
        }
        return $this->paths[$namespace];
    }

    protected function parseTemplateNamespace($template)
    {
        if (preg_match('#^(?P<namespace>[^:]+)::(?P<template>.*)$#', $template, $matches)) {
            return $matches;
        }
        return ['namespace' => self::DEFAULT_NAMESPACE, 'template' => $template];
    }
}
