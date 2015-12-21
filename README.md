# template-resolver

Namespaced template resolver for templating systems that do not provide one.

[![Build Status](https://travis-ci.org/kynx/template-resolver.svg?branch=master)](https://travis-ci.org/kynx/template-resolver)

## Features

* Filesystem resolver can be configured to search multiple paths for templates
* Cache resolver uses any [PSR-6](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-6-cache.md)
  compliant caching library
* Aggregate resolver will search attached resolvers by priority, returning first match
* Extensible: attach your own resolvers to handle loading templates from DB, etc.
* No (non-dev) dependencies except for PSR-6



## Installation

Install via [Composer](http://getcomposer.com):

```
composer require kynx/template-resolver
```

## Basic usage

The simplest usage is with a single filesystem resolver. This will resolve the content of templates it finds on the
paths you specify. Assuming you have a template engine called `MyRenderer`:

```php
$renderer = new MyRenderer();
$resolver = new FilesystemResolver();
$resolver->addPath('/path/to/templates')
    ->setExtension('tpl');
$template = $resolver->resolve('test');
echo $renderer->render((string) $template);
// will output the contents of '/path/to/templates/test.tpl'
```

Namespaces enable you to separate your templates out into distinct search paths:

```php
$renderer = new MyRenderer();
$resolver = new FilesystemResolver();
$resolver->addPath('/path/to/templates')
    ->addPath('/path/to/namespace', 'mynamespace')
    ->setExtension('tpl');
$template = $resolver->resolve('mynamesapce::test');
echo $renderer->render((string) $template);
// will output the contents of '/path/to/namespace/test.tpl'
```

The `mynamespace::test` syntax tells the resolver to first search for the template in the `mynamespace` path(s). If it is
not found there the default namespace will be searched.

## Caching templates

To speed up subsequent lookups for templates you can store them in any [PSR-6](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-6-cache.md)
compliant caching engine. The example below uses [bravo3/cache](https://github.com/bravo3/cache). The 
`AggregateResolver` enables you to search the cache for templates first before hitting the filesystem:

```php
$resolver = new AggregateResolver();

$cachePool = new RedisCachePool('tcp://10.0.0.1:6379');
$cacheResolver = new CacheResolver($cachePool);

$fileResolver = new FilesystemResolver();
$fileResolver->addPath('/path/to/templates')
    ->addPath('/path/to/namespace', 'mynamespace')
    ->setExtension('tpl');

$resolver->accept($cacheResolver, 1);
$resolver->accept($fileResolver, 0);

$result = $resolver->resolve('mynamespace::test');
if (! $result->isCached()) {
    // store the result in cache
    $resolver->save($result->getKey(), (string) $result);
}
echo (string) $result;
// outputs temple contents

```

The second argument to `AggregateResolver::accept()` determines the priority: higher values are searched first, and first
result found is returned. Note that it is up to your implementation to store the result back in the cache.

## Compiled templates

Many template systems can compile templates to speed up subsequent processing. The compiled template may be an array of
tokens, a PHP function or, as in my own [Handlebars implementation](https://github.com/kynx/v8js-handlebars), javascript.
All of these make an excellent candidate for caching. Extending the above example:

```php
// ...
$cacheResolver = new CacheResolver($cachePool);
$cachResolver->setIsCompiled(true);
// ...
$result = $resolver->resolve('mynamesapce::test');
if ($result->isCompiled()) {
    $compiled = $result->getContents();
} else {
    // compile the result
    $compiled = $handlebars->precompile((string) $result);
    $resolver->save($result->getKey(), $compiled);
}
// do something with your compiled template
```

The `AggregateResolver` will save the result in the first resolver in it's queue that supports the `save()` method.

## `Result` objects

As shown in the examples above, the return value of `ResolverInterfae::resolve()` is an object with a `__toString()`
convenience method for accessing the content. Other methods of interest are:

* `getContent()`: returns the template content. If your compiled template is an array, use this instead of `(string) $result`
* `getKey()`: this returns the `namespace::template` that was actually matched. So if you request `mynamespace::test`
  which does not exist in `mynamespace` but is found in the default namespace, the key will look something like
  `__DEFAULT__::test`. Using this as the cache key will result in two cache hits for subsequent searches, but will
  ensure duplicates are not stored in cache.
* `isCached()`: returns true if the content was found in a caching resolver
* `isCompiled()`: returns true if the resolver was marked as a compiled resolver via `setIsCompiled()`

## Extending

To create other resolvers - for instance, to fetch templates from a DB - implement the `ResolverInterface`. This contains
only two methods, `resolve()` and `setIsCompiled()`.

If your resolver can handle multiple paths it should implement the `PathedResolverInterface`, which augments the above
with an `addPath()` and `getPaths()` method.

If your resolver supports saving results back, implement the `SavingResolverInterface`.

For convenience there is an `AbstractResolver` class you can extend, which contains some useful utility methods.

## Credits

Much of the inspiration from this, and much of the `AggregateResolver` code, came from [phly-mustache](https://github.com/weierophinney/phly-mustache)'s
template resolver.
