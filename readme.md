# Monolog Sticky Context Processor

The Sticky Context Processor is a Monolog processor that allows you to configure data
that should always be attached to your log messages in the context data (the `extra`
key on the log record).

This is similar to pushing a custom processor onto your Monolog Logger instance every
time you want to attach context data to all log records. With a custom Monolog processor,
it would look something like this:

```php
<?php

$logger = new Monolog\Logger('sticky');
$logger->pushProcessor(function ($record) {
    $record['extra']['user'] = user()->id ?? null;
});
```

However, this has several flaws:

1. Why should we log a `null` user if they're not present, we probably don't want to log a user at all for our public routes
2. What do we do if we conditionally want to add data _later on_? What if we don't want to add the authenticated user's ID until we have authenticated them?
3. When we have attached data, what if we want to exclude it from the context data for a given log record?

This package provides a simple Monolog Processor which allows you to store *and update*
your "sticky" context data. With our processor, this is what the above snippet would look like:

```php
<?php
// ...

$logger = new Monolog\Logger('sticky');
$logger->pushProcessor(new StickyContextProcessor);

StickyContext::add('user', user()->id ?? null);
```

## Installation

Install the package by requiring the package with composer:
```
composer require decahedron/monolog-sticky-context
```

## Usage
Simply push the `StickyContextProcessor` onto your Monolog instance in the following way:

```php
<?php
use Decahedron\StickyLogging\StickyContextProcessor;

$logger = new Monolog\Logger('sticky');
$logger->pushProcessor(new StickyContextProcessor);
```

After that, you only have to worry about interacting with the `StickyContext` object.

You can add new data by calling `add`:
```php
if (auth()->check()) {
    StickyContext::add('user', user()->id)
}

// $logger->level(...) will now attach the sticky context data in `extra`
```

If you for some reason want to exclude your sticky data for a certain portion of code,
you may do that using the `enable` and `disable` methods:

```php
StickyContext::disable();
$logger->debug(...);
StickyContext::enable();
```

You may also clear all sticky context data by calling `flush`:
```
StickyContext::add('user', 1);
$logger->info('Something happened');        // This will include the user id in the context
StickyContext::flush();

$logger->info('Something else happened');   // The sticky context is now empty and not included
```