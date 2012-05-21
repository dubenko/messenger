<?php
class App_Controller_Plugin_ModulesPlugins extends Zend_Controller_Plugin_Abstract
{
    //load specific plugins for modules
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $module = $request->getModuleName();
        $acl = Zend_Registry::get('acl');
        $storageSessionName = 'default';

        if ($module == 'admin')
        {
            $storageSessionName = 'admin';

            //add resources
            $acl->addResource(new Zend_Acl_Resource('user'));
            $acl->addResource(new Zend_Acl_Resource('message'));

            $acl->addRole(new Zend_Acl_Role('guest'));
            $acl->addRole(new Zend_Acl_Role('user'), 'guest');
            $acl->addRole(new Zend_Acl_Role('admin'), 'user');

            $acl->deny('guest', null, null);
            $acl->allow('guest', 'user', array('login', 'logout'));
            $acl->allow('admin', null, null);
        }
        else
        {
            //add resources
            $acl->addResource(new Zend_Acl_Resource('user'));
            $acl->addResource(new Zend_Acl_Resource('message'));
            $acl->addResource(new Zend_Acl_Resource('error'));

            $acl->addRole(new Zend_Acl_Role('guest'));
            $acl->addRole(new Zend_Acl_Role('user'), 'guest');

            $acl->deny('guest', null, null);
            $acl->allow('guest', 'user', array('login', 'logout'));
            $acl->allow('user', null, null);
        }

        Zend_Layout::getMvcInstance()->auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($storageSessionName))->getIdentity();
    }
}
