# template-resolver

Namespaced template resolver for templating systems that do not provide one.

## Features

* Filesystem resolver can be configured to search multiple paths for templates
* Cache resolver uses any [PSR-6](https://github.com/php-fig/fig-standards/blob/master/proposed/cache.md)
  compliant caching library
* Aggregate resolver will search attached resolvers by priority, returning first match
* Extensible: attach your own resolvers to handle loading templates from DB, etc.
* No (non-dev) dependencies

## Installation

Install via [Composer](http://getcomposer.com):

```
composer require kynx/template-resolver
```

## Basic usage

The simplest usage is with a single filesystem resolver. This will resolve the content of templates it finds on the
paths you specify. Assuming you have a temlate engine called `MyRenderer`:

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

The `mynamespae::test` syntax tells the resolver to first search for the template in the `mynamespac` path(s). If it is
not found there the default namespace will be searched.

## Caching templates

To speed up subsequent lookups for templates you can store them in a [PSR-6](https://github.com/php-fig/fig-standards/blob/master/proposed/cache.md)
compliant caching engine. The `AggregateResolver` enables you to search the cache for templates first before hitting the
filesystem:

```php
To come
```
## `Result` objects

To come

## Extending

To come
## Credits

Much of the inspiration from this, and much of the `AggregateResolver` code, came from [phly-mustache](https://github.com/weierophinney/phly-mustache)'s
template resolver.
