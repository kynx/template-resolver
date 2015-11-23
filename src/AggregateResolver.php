<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 * @author Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @see https://github.com/phly/phly-mustache/blob/master/src/Resolver/AggregateResolver.php
 */

namespace Kynx\Template\Resolver;

use Countable;
use IteratorAggregate;
use Kynx\Template\Resolver\Exception\ResolverTypeNotFoundException;
use SplPriorityQueue;

class AggregateResolver implements Countable, IteratorAggregate, PathedResolverInterface, SavingResolverInterface
{
    /**
     * @var SplPriorityQueue
     */
    private $queue;

    /**
     * Constructor.
     *
     * Creates the internal priority queue.
     */
    public function __construct()
    {
        $this->queue = new SplPriorityQueue();
    }

    /**
     * Returns result from first resolver that matches
     *
     * @param  string $template
     * @return Result|null
     */
    public function resolve($template)
    {
        foreach ($this as $resolver) {
            $resource = $resolver->resolve($template);
            if (! is_null($resource)) {
                return $resource;
            }
        }
        return null;
    }

    public function save($template, $contents)
    {
        foreach ($this as $resolver) {
            if ($resolver instanceof SavingResolverInterface) {
                return $resolver->save($template, $contents);
            }
        }
        throw new Exception\ResolverTypeNotFoundException(
            sprintf("No resolvers implement %s", SavingResolverInterface::class)
        );
    }

    /**
     * Return count of attached resolvers
     *
     * @return int
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * IteratorAggregate: return internal iterator.
     *
     * @return SplPriorityQueue
     */
    public function getIterator()
    {
        // return clone so iterating doesn't trash heap
        return clone $this->queue;
    }

    /**
     * Attach a resolver
     *
     * @param  ResolverInterface $resolver
     * @param  int $priority
     * @return self
     */
    public function attach(ResolverInterface $resolver, $priority = 1)
    {
        $this->queue->insert($resolver, $priority);
        return $this;
    }

    public function addPath($path, $namespace = null)
    {
        foreach ($this as $resolver) {
            if ($resolver instanceof PathedResolverInterface) {
                $resolver->addPath($path, $namespace);
                return true;
            }
        }
        throw new ResolverTypeNotFoundException(
            sprintf("No resolvers implement %s", PathedResolverInterface::class)
        );
    }

    public function getPaths()
    {
        foreach ($this as $resolver) {
            if ($resolver instanceof PathedResolverInterface) {
                return $resolver->getPaths();
            }
        }
        throw new ResolverTypeNotFoundException(
            sprintf("No resolvers implement %s", PathedResolverInterface::class)
        );
    }

    /**
     * Does the aggregate contain a resolver of the specified type?
     *
     * @param string $type
     * @return bool
     */
    public function hasType($type)
    {
        foreach ($this as $resolver) {
            if ($resolver instanceof $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fetch one or more resolvers that match the given type.
     *
     * @param string $type
     * @return ResolverInterface Return the matched instance, or an aggregate
     *     containing all matched instances.
     * @throws Exception\ResolverTypeNotFoundException if no resolvers of the type are found.
     */
    public function fetchByType($type)
    {
        if (! $this->hasType($type)) {
            throw new Exception\ResolverTypeNotFoundException();
        }
        $resolvers = new self();
        foreach ($this as $resolver) {
            if ($resolver instanceof $type) {
                $resolvers->attach($resolver);
            }
        }
        if (1 === count($resolvers)) {
            return $resolvers->queue->extract();
        }
        return $resolvers;
    }

    public function setIsCompiled($isCompiled)
    {
        throw new Exception\BadMethodCallException("Aggregate resolvers cannot be marked as compiled");
    }
}
