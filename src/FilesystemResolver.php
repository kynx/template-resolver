<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

use SplStack;

final class FilesystemResolver extends AbstractResolver implements PathedResolverInterface
{
    private $extension = 'template';

    private $separator = '/';

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

    /**
     * @param $template
     * @return Result|null
     */
    public function resolve($template)
    {
        $resolvedTemplate = $this->parseTemplateNamespace($template);
        $namespace = $resolvedTemplate['namespace'];
        $template  = $resolvedTemplate['template'];

        $segments = explode($this->getSeparator(), $template);
        $template = implode('/', $segments) . '.' . $this->getExtension();

        $result = $this->fetchResultForNamespace($template, $namespace);
        if ($result || $namespace === self::DEFAULT_NAMESPACE) {
            return $result;
        }

        return $this->fetchResultForNamespace($template, self::DEFAULT_NAMESPACE);
    }

    /**
     * Add a (optionally namespaced) template path to the resolver
     * @param string $path
     * @param string|null $namespace
     * @return $this
     */
    public function addPath($path, $namespace = null)
    {
        if (! is_dir($path)) {
            throw new Exception\InvalidPathException("Template path '$path' does not exist");
        }

        if (null !== $namespace && ! is_string($namespace)) {
            throw new Exception\InvalidNamespaceException('Namespace must be a string');
        }

        $namespace = $namespace ?: self::DEFAULT_NAMESPACE;
        $path = rtrim((string) $path, '/\\');
        $this->getPath($namespace)->push($path);

        return $this;
    }

    /**
     * @param $namespace
     * @return SplStack
     */
    private function getPath($namespace)
    {
        if (!isset($this->paths[$namespace])) {
            $this->paths[$namespace] = new SplStack();
        }
        return $this->paths[$namespace];
    }

    /**
     * Attempt to retrieve a result for a given namespace.
     *
     * @param string $template
     * @param string $namespace
     * @return Result|null
     */
    private function fetchResultForNamespace($template, $namespace)
    {
        foreach ($this->getPath($namespace) as $path) {
            if (! empty($path)) {
                $path .= '/';
            }
            $filename = $path . $template;
            $contents = @file_get_contents($filename);
            if ($contents !== false) {
                $key = $namespace . '::' . $template;
                return new Result($key, $contents, false, $this->isCompiled());
            }
        }
        return null;
    }

    public function getPaths()
    {
        return $this->paths;
    }
}
