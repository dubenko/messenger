<?php
error_reporting(E_ALL);
$root = dirname(dirname(__FILE__));

defined('APPLICATION_PATH')
|| define('APPLICATION_PATH',
realpath(dirname(__FILE__) . '/../application'));

defined('APPLICATION_ROOT')
|| define('APPLICATION_ROOT',
realpath(dirname(__FILE__) . '/../'));

set_include_path(
    $root . '/library' . PATH_SEPARATOR
    . $root . '/application' . PATH_SEPARATOR
    . get_include_path()
);

$ip = gethostbyname(php_uname("n"));
if (in_array($ip, array('test.messenger.com')))
{
    define('APPLICATION_ENV', "test");
}
else if (in_array($ip, array('www.messenger.com')))
{
    define('APPLICATION_ENV', "production");
}
else
{
    define('APPLICATION_ENV', "development");
}
unset($ip);

setlocale(LC_TIME, 'ru_RU');

require 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/' . APPLICATION_ENV . '.php'
);

$application->bootstrap()
            ->run();
