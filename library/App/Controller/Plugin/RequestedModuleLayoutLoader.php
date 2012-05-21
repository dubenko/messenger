<?php
class App_Controller_Plugin_RequestedModuleLayoutLoader extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $config = Zend_Registry::get('config');
        $moduleName = $request->getModuleName();

        if (isset($config->{$moduleName}->resources->layout->layout))
        {
            $layoutScript = $config->{$moduleName}->resources->layout->layout;
            Zend_Layout::getMvcInstance()->setLayout($layoutScript);
        }

        if (isset($config->{$moduleName}->resources->layout->layoutPath))
        {
            $layoutPath = $config->{$moduleName}->resources->layout->layoutPath;
            $moduleDir = Zend_Controller_Front::getInstance()->getModuleDirectory();
            Zend_Layout::getMvcInstance()->setLayoutPath($moduleDir . DIRECTORY_SEPARATOR . $layoutPath);
        }
    }
}
