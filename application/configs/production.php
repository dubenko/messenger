<?php
return array(
    'phpSettings' => array(
        'display_startup_errors' => 0,
        'display_errors' => 0,
    ),
    'db' => array(
        'adapter' => 'PDO_MYSQL',
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'ghbdtn',
        'dbname' => 'messenger',
    ),
    'host' => 'http://www.messenger.com/',
    'bootstrap' => array(
        'path' => APPLICATION_PATH . '/Bootstrap.php',
        'class' => 'Bootstrap',
    ),
    'autoloadernamespaces' => array(
        'App',
    ),
    'resources' => array(
        'modules' => array(
            '',
        ),
        'frontController' => array(
            'moduleDirectory' => APPLICATION_PATH . '/modules',
            'moduleControllerDirectoryName' => 'controllers',
            'params' => array(
                'prefixdefaultmodule' => 1,
                'displayExceptions' => 0,
            ),
            'defaultControllerName' => 'user',
            'defaultAction' => 'index',
            'defaultModule' => 'default',
            'plugins' => array(
                'layoutloader' => 'App_Controller_Plugin_RequestedModuleLayoutLoader',
                'moduleplugins' => 'App_Controller_Plugin_ModulesPlugins',
            ),
            'throwExceptions' => 0,
        ),
        'view' => array(
            'helperPath' => array(
                'App_View_Helper' => 'App/View/Helper',
            ),
        ),
        'layout' => array(
            'layout' => 'default/base',
            'layoutPath' => APPLICATION_PATH . '/layouts',
        ),
//        'cacheManager' => array(
//            'primary' => array(
//                'frontend' => array(
//                    'name' => 'Core',
//                    'options' => array(
//                        'lifetime' => 7200,
//                        'automatic_serialization' => 1,
//                        'logging' => 0,
//                    ),
//                ),
//                'backend' => array(
//                    'name' => 'Memcached',
//                    'options' => array(
//                        'servers' => array(
//                            array(
//                                'host' => 'localhost',
//                                'port' => 11211,
//                                'persistent' => '',
//                            ),
//                        ),
//                    ),
//                ),
//            ),
//        ),
    ),
    'admin' => array(
        'resources' => array(
            'layout' => array(
                'layout' => 'admin/base',
            ),
        ),
    ),
    'site' => array(
        'cookieDomain' => '.messenger.com',
    ),
    'baseDomain' => 'www.messenger.com',
    'sharedScripts' => array(
        '/js/jquery-1.7.2.min.js',
        '/js/jquery.cooquery.min.js',
        '/js/jquery.validate.min.js',
        '/js/jquery.validate.additional-methods.min.js',
        '/js/jquery.validate.messages_ru.js',
        '/js/jquery-ui-1.8.20.custom.min.js',
        '/js/default/user.js',
        '/js/default/message.js',
    ),
    'sharedStyles' => array(
        '/css/style.css',
        '/css/jquery-ui-1.8.20.custom.css',
    ),
    'maintenance' => array(
        'public' => 1,
        'allowIP' => '127.0.0.1',
    ),
);
