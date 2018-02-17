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
```php
StickyContext::add('user', 1);
$logger->info('Something happened');        // This will include the user id in the context
StickyContext::flush();

$logger->info('Something else happened');   // The sticky context is now empty and not included
```

If you need to attach data that may change over time, and hence requires a callback to retrieve this data,
you may specify a callback as the second value:

```php
StickyContext::add('user', function () {
    return user()->id;
});
```

Closures are evaluated every time the processor runs. As such, the data does not get cached between log messages.
This means that you can add this sticky context data at instantiation (which can be useful for packages).
However, as it is executed every time, it's also slightly less performant than storing a static value.

### Stacks

If you have the need to separate your sticky data into multiple different keys in Monolog's `extra` array,
you may use Stacks. A stack holds a collection of sticky data, and you may use multiple stacks to separate your data.

One possible use case for this is where you have (internal) packages that add sticky data to your logs.
In this case, you might want to separate what sticky data is set by the package, and what is set by the application.
By making the package log to its own Stack, the data will appear under a specified key in the `extra` array of the record.

#### Example

```php
// Somewhere in the package
StickyContext::stack('my_package')->add('request_id', Uuid::uuid4()->toString());

// In the application
StickyContext::add('user', user()->id);
```
This will push new sticky data to the `my_package` stack. When the message gets logged, your log message's `extra` key will look as follows:

```php
[
    'sticky' => [
        'user' => 1,
    ],
 
    'my_package' => [
        'request_id' => '91c9dc1e-11ed-46ff-8598-701a9f93eb2b',
    ],
]
```

#### The Default Stack

A Stack is the underlying object used for storing context data. When you call `StickyContext::add()`, the call is
in reality proxied to `StickyContext::stack('sticky')->add()`. This is referred to as the _default_ stack. You
may change the default stack by calling `StickyContext::defaultStack()`.

```php
StickyContext::defaultStack('application');
```

Now, when adding context data and then sending log messages, the sticky data will be added under `['extra']['application']`,
rather than the default `['extra']['sticky']`.

Note that when changing the default stack, any sticky data you have previously added **is preserved**, it is simply moved
to the new stack name.