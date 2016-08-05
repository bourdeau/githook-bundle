GitHook Bundle
========

About GitHook Bundle
---------------

GitHook Bundle Bundle is a PHP 7 library

Installation
------------

## Prerequisites

A Symfony3 project

## With composer

This bundle can be installed using [composer](http://getcomposer.org) by adding the following in the `require` section of your `composer.json` file:

``` json
    "require": {
        ...
        "bourdeau/githook-bundle": "~0.1"
    },
```

## Register the bundle

You must register the bundle in your kernel:

``` php
<?php

// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Bourdeau\Bundle\GitHookBundle\GitHookBundle(),
    ];
    // ...
}
```

Configuration
-------------
There is no configuration for now.
