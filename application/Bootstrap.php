<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function _initMySQL()
    {
        $config = $this->getOptions();
        $db = Zend_Db::factory($config['db']['adapter'], $config['db']);
        $db->query('SET NAMES "utf8"');
        Zend_Registry::set('database', $db);
        Zend_Db_Table::setDefaultAdapter($db);
    }

    public function _initAcl()
    {
        Zend_Registry::set('acl', new Zend_Acl());
    }

    public function _initAuth()
    {
        Zend_Session::setOptions(array(
            'gc_maxlifetime' => 2764800,
        ));
    }

    public function _initConfig()
    {
        Zend_Registry::set('config', new Zend_Config($this->getOptions()));
        Zend_Registry::set('documentRoot', APPLICATION_ROOT);
    }

    public function _initRegistry()
    {
//Отключаем кеш за ненадобностью, в конфиге тоже закоментированый блок
//        $this->bootstrap('cachemanager');
//        $backend = $this->getResource('cachemanager')->getCache('primary');
//        Zend_Registry::set('cache', $backend);
    }

    public function _initSessions()
    {
        $this->bootstrap('registry');
        $config = array(
            'name' => 'session',
            'primary' => 'id',
            'modifiedColumn' => 'modified',
            'dataColumn' => 'data',
            'lifetimeColumn' => 'lifetime',
        );
        Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_DbTable($config));
    }

    public function _initRoutes()
    {
        $this->bootstrap('frontController');
        $frontController = Zend_Controller_Front::getInstance();
        $routes = include APPLICATION_PATH . '/configs/routes.php';
        $router = new Zend_Controller_Router_Rewrite();
        foreach ($routes as $routeName => $route)
        {
            $router->addRoute($routeName, $route);
        }
        $frontController->setRouter($router);
    }

    public function _initPlugins()
    {
        $translator = new Zend_Translate(
            'array',
            '../resources/languages',
            'ru',
            array('scan' => Zend_Translate::LOCALE_DIRECTORY)
        );
        Zend_Validate_Abstract::setDefaultTranslator($translator);

        $this->bootstrap('acl');

        $frontController = Zend_Controller_Front::getInstance();
        $frontController->registerPlugin(new App_Controller_Plugin_Auth(Zend_Registry::get('acl'), Zend_Auth::getInstance()), 400);
    }

    protected function _initAutoload()
    {
        $moduleLoader = new Zend_Application_Module_Autoloader(
            array(
                'namespace' => '',
                'basePath'  => APPLICATION_PATH,
            )
        );

        return $moduleLoader;
    }

    protected function _initView()
    {
        $view = new Zend_View();
        $view->doctype('HTML5');

        $view->env = APPLICATION_ENV;
        $view->addHelperPath(APPLICATION_ROOT . '/library/App/View/Helper', 'App_View_Helper');
        $view->addFilterPath(APPLICATION_ROOT . '/library/App/View/Filter', 'App_View_Filter');

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);

        if (APPLICATION_ENV == 'development')
        {
            App_View_Helper_HeadRevisionLink::setConfig(APPLICATION_ROOT . '/public/css/static', 0, 0);
            App_View_Helper_HeadRevisionScript::setConfig(APPLICATION_ROOT . '/public/js/static', 0, 0);
        }
        else
        {
            App_View_Helper_HeadRevisionLink::setConfig(APPLICATION_ROOT . '/public/css/static');
            App_View_Helper_HeadRevisionScript::setConfig(APPLICATION_ROOT . '/public/js/static');
        }

        return $view;
    }

    protected function _initCheckIP()
    {
        $config = $this->getOptions();
        if ($config['maintenance']['public'] != 1 && !in_array($_SERVER['REMOTE_ADDR'], explode(',', $config['maintenance']['allowIP'])))
        {
            exit('Maintenance Mode - SITE is currently undergoing scheduled maintenance. Please try back in 60 minutes. Sorry for the inconvenience.');
        }
    }
}
