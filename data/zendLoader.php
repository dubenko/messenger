<?php

/**
 * Загрузчик зендовских классов
 * Включите этот файл для того, чтоб пользоваться зендом из командной строки
 */

$root = dirname(dirname(__FILE__));

defined('APPLICATION_PATH')
|| define('APPLICATION_PATH',
realpath(dirname(__FILE__) . '/../application'));

defined('APPLICATION_ROOT')
|| define('APPLICATION_ROOT',
realpath(dirname(__FILE__) . '/../'));

# Set the include path to use your ZF app
set_include_path(
    $root . '/library' . PATH_SEPARATOR
    . $root . '/application' . PATH_SEPARATOR
    . APPLICATION_ROOT . '/models' . PATH_SEPARATOR
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

require_once 'Zend/Loader/Autoloader.php';
require_once 'Zend/Loader/Autoloader/Resource.php';

$res = array(
    'basePath' => APPLICATION_ROOT,
    'namespace' => '',
    'resourceTypes' => array(
        'model' => array(
            'path' => 'application/models',
            'namespace' => 'Model',
        ),
        'app' => array(
            'path' => 'library/App',
            'namespace' => 'App',
        ),
    )
);
$loader = new Zend_Loader_Autoloader_Resource($res);

$config = require_once APPLICATION_PATH . '/configs/' . APPLICATION_ENV . '.php';

$db = Zend_Db::factory($config['db']['adapter'], $config['db']);
$db->query("SET NAMES 'utf8'");
Zend_Registry::set('database', $db);
Zend_Db_Table::setDefaultAdapter($db);
$manager = new Zend_Cache_Manager;
$manager->setCacheTemplate('primary', $config['resources']['cacheManager']['primary']);
$backend = $manager->getCache('primary');
Zend_Registry::set('cache', $backend); //Не убивать, модели пользуются кэшем из регистра
Zend_Registry::set('config', $config);
