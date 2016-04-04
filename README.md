# SContainer
Simple Container for Expressive

Simple Container created for understanding (basic) Container mechanism in Zend Expressive

Installation:

```
composer require 'gabidj/scontainer'
```


Security notice:
-------
 - SContainer was made to help understanding how Container/Service Management works in Zend Expressive 
 - You should NOT use SContainer in production environment


Usage
-------

* Sample Route configuration:

```
<?php

use GabiDJ\Expressive\SContainer\SContainer as Container ;

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container with given config
$container = new Container($config);

return $container;

```
 Your /config/container.php file should reflect these settings in order to work with SContainer
 

Support
-------
 * Ask me anything about this project: twitter.com/GabiSuciu 
 * For support or suggestions visit: www.dotkernel.com/blog/ 