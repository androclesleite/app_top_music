<?php

use Illuminate\Foundation\Application;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Set The Application Environment
|--------------------------------------------------------------------------
|
| Laravel needs to know what environment it is running in so it knows how
| to respond to requests. The testing environment is automatically set.
|
*/

Application::setDefaultConfiguration(__DIR__.'/../');