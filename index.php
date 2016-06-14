<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Load configuration
require __DIR__ .'/config/config.php';

// Load composer packages
require 'vendor/autoload.php';

// Load models
//spl_autoload_register('controller::loadClass');
/*require __DIR__ .'/models/db.class.php';
require __DIR__ .'/models/curl.class.php';
require __DIR__ .'/models/monolog.class.php';
require __DIR__ .'/models/users.class.php';
require __DIR__ .'/models/xsl.class.php';*/

// Load controller
require 'controllers/main.php';

// Load models
spl_autoload_register('controller::loadClass');

//Load content
new controller();
