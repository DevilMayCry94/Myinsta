<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
define("URL_AUTH","https://www.Facebook.com/dialog/oauth");
define("CLIENT_ID","1639489529651548");
define("SECRET","60ef464920adbc20e90588249977ee85");
define("REDIRECT","http://dokuen-lx.nixsolutions.com/social/facebook");
define("TOKEN","https://graph.Facebook.com/oauth/access_token");
define("GET_DATA","https://graph.Facebook.com/me");
define("BASE_PATH",__DIR__);
error_reporting(E_ALL);
ini_set("display_errors", 1);
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Setup autoloading
require 'init_autoloader.php';
session_start();
//require_once '../lib/SocialAuther/autoload.php';
// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
