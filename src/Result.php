<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace Kynx\Template\Resolver;

/**
 * Template resolver result, including details about what was matched
 */
final class Result
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $contents;
    /**
     * @var bool
     */
    private $isCached;
    /**
     * @var bool
     */
    private $isCompiled;

    /**
     * Constructor
     *
     * @param string $key
     * @param string $contents
     * @param bool $isCompiled
     */
    public function __construct($key, $contents, $isCached, $isCompiled)
    {
        $this->key = $key;
        $this->contents = $contents;
        $this->isCached = $isCached;
        $this->isCompiled = $isCompiled;
    }

    /**
     * Returns contents of match as string
     * @return string
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * Returns contents of match as string
     * @return string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Returns key that matched
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns true if contents come from cache
     * @return string
     */
    public function isCached()
    {
        return $this->isCached;
    }

    /**
     * Returns true if contents are compiled
     * @return bool
     */
    public function isCompiled()
    {
        return $this->isCompiled;
    }
}
