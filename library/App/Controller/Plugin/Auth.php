<?php
class App_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    private $_auth;
    private $_acl;

    protected $_noAuthDefault = array(
        'module' => 'default',
        'controller' => 'user',
        'action' => 'login'
    );

    protected $_noAclDefault = array(
        'module' => 'default',
        'controller' => 'error',
        'action' => 'deny'
    );

    protected $_noAuthAdmin = array(
        'module' => 'admin',
        'controller' => 'user',
        'action' => 'login'
    );

    protected $_noAclAdmin = array(
        'module' => 'default',
        'controller' => 'error',
        'action' => 'deny'
    );

    public function __construct($acl, $auth)
    {
        $this->_auth = $auth;
        $this->_acl = $acl;
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if ($this->_response->isException())
        {
            return;
        }

        if ($request->getModuleName() == 'admin')
        {
            $this->_noAclDefault = $this->_noAclAdmin;
            $this->_noAuthDefault = $this->_noAuthAdmin;
        }

        if (Zend_Session::isStarted() && $this->_auth->hasIdentity())
        {
            if ($request->getModuleName() == 'admin')
            {
                $role = $this->_auth->getIdentity()->role;
            }
            else
            {
                $role = 'user';
            }
        }
        else
        {
            $role = 'guest';
        }

        $controller = $request->controller;
        $action = $request->action;
        $module = $request->module != null ? $request->module : 'default';
        $resource = $request->controller;

        if ($this->_acl->has($resource))
        {
            $resource = null;
        }

        if (!$this->_acl->isAllowed($role, $controller, $action))
        {
            list ($module, $controller, $action) = !$this->_auth->hasIdentity()
                ? array_values($this->_noAuthDefault)
                : array_values($this->_noAclDefault);
        }

        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);
    }
}
