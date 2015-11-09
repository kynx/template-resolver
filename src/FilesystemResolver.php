<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

final class FilesystemResolver extends AbstractResolver
{
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
    public function addTemplatePath($path, $namespace = null)
    {
        if (! is_dir($path)) {
            throw new Exception\InvalidPathException("Template path '$path' does not exist");
        }

        return parent::addTemplatePath($path, $namespace);
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
        foreach ($this->getTemplatePath($namespace) as $path) {
            if (! empty($path)) {
                $path .= '/';
            }
            $filename = $path . $template;
            $contents = @file_get_contents($filename);
            if ($contents !== false) {
                return new Result($filename, $contents, $this->isCompiled());
            }
        }
        return null;
    }
}
