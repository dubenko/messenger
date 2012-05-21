<?php
class Admin_UserController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->headLink()->appendStylesheet('/css/style.css');
        $this->view->headLink()->appendStylesheet('/css/jquery-ui-1.8.20.custom.css');

        $this->view->headScript()->appendFile('/js/jquery-1.7.2.min.js');
        $this->view->headScript()->appendFile('/js/jquery-ui-1.8.20.custom.min.js');
        $this->view->headScript()->appendFile('/js/jquery.validate.min.js');
        $this->view->headScript()->appendFile('/js/jquery.validate.additional-methods.min.js');
        $this->view->headScript()->appendFile('/js/jquery.validate.messages_ru.js');
        parent::init();
    }

    public function indexAction()
    {
        $limit = 10;
        $page  = $this->_request->getParam("page", 1);

        $sort = $this->_request->getParam("sort", "id");
        $order = $this->_request->getParam("order", "asc");

        $modelUser = new Model_User();
        $select = $modelUser->getAdapter()
                            ->select()
                            ->from(array('user'))
                            ->joinLeft(array('message'), 'message.from = user.login', array('messageCount' => 'COUNT(message.id)'))
                            ->group('user.id');

        switch ($sort)
        {
            case 'id':
                $select = $select->order('id '.$order);
                break;
            case 'login':
                $select = $select->order('login '.$order);
                break;
            case 'role':
                $select = $select->order('role '.$order);
                break;
            case 'messageCount':
                $select = $select->order('messageCount '.$order);
                break;
            case 'createDate':
                $select = $select->order('createDate '.$order);
                break;
        }

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);

        $this->view->sort = $sort;
        $this->view->order = $order;
        $this->view->users = $paginator->getCurrentItems();
        $this->view->paginator = $paginator;
        $this->view->count = $paginator->getCurrentItemCount();
        $this->view->limit = $limit;
    }

    public function addAction()
    {
        $this->view->headScript()->appendFile('/js/admin/user.js');
        if ($this->_request->isPost())
        {
            $form = $this->_getAddForm();
            if ($form->isValid($this->_getAllParams()))
            {
                $data = array(
                    'login' => $form->getValue('login'),
                    'password' => MD5($form->getValue('password')),
                    'role' => $form->getValue('role'),
                    'createDate' => new Zend_Db_Expr('NOW()'),
                );
                $modelUser = new Model_User();
                $modelUser->insert($data);
                $this->_redirect("/admin");
            }
            $this->view->data = $this->_getAllParams();
            $this->view->error = $form->getMessages();
        }
    }

    public function updateAction()
    {
        $this->view->headScript()->appendFile('/js/admin/user.js');
        $userId = $this->_request->getParam("userId", null);
        if ($userId == null)
        {
            throw new Zend_Controller_Action_Exception("User not found", 404);
        }
        $modelUser = new Model_User();
        $user = $modelUser->getUser($userId);
        $this->view->user = $user;

        if ($this->_request->isPost())
        {
            $form = $this->_getUpdateForm($user);
            if ($form->isValid($this->_getAllParams()))
            {
                $data = array(
                    'role' => $form->getValue('role'),
                );
                if ($form->getValue('login') != $user['login'])
                {
                    $data['login'] = $form->getValue('login');
                }
                if ($form->getValue('password'))
                {
                    $data['password'] = MD5($form->getValue('password'));
                }
                $modelUser->updateUser($userId, $data);
                $this->_redirect("/admin");
            }
            $this->view->data = $this->_getAllParams();
            $this->view->error = $form->getMessages();
        }
    }

    public function deleteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $userId = $this->getRequest()->getParam('userId', null);
        if ($userId != null)
        {
            $modelUser = new Model_User();
            $admin = $modelUser->getCurrentAdmin();
            $modelUser->deleteUser($userId);
            if ($userId == $admin->id)
            {
                $storage = Zend_Auth::getInstance()->getStorage();
                Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'))->clearIdentity();
                Zend_Auth::getInstance()->setStorage($storage);
            }
        }

        $this->_redirect("/admin");
    }

    public function loginAction()
    {
        Zend_Layout::getMvcInstance()->setLayout("admin/empty");
        $this->view->headScript()->appendFile('/js/admin/user.js');

        if ($this->_request->isPost())
        {
            $form = $this->_getLoginForm();
            if ($form->isValid($this->_getAllParams()))
            {
                $data = $form->getValues();
                $modelUser = new Model_User();
                $authAdapter = new Zend_Auth_Adapter_DbTable($modelUser->getAdapter(), 'user');
                $authAdapter->setIdentityColumn('login')
                            ->setCredentialColumn('password')
                            ->setIdentity($data['login'])
                            ->setCredential(MD5($data['password']));

                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);

                if ($result->isValid())
                {
                    $data = $authAdapter->getResultRowObject(null, "password");
                    $auth->getStorage()->write($data);

                    Zend_Session::rememberMe(60 * 60 * 24 * 32);
                    $this->_redirect("/admin");
                }
                else
                {
                    $this->view->errorLoginForm = 'Не верный адрес электронной почты или пароль, попробуйте еще раз!';
                }
            }
            $this->view->data = $this->_getAllParams();
            $this->view->error = $form->getMessages();
        }
    }

    public function logoutAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect("/admin");
    }

    protected function _getLoginForm()
    {
        $form = new Zend_Form();

        $login = new Zend_Form_Element_Text('login');
        $login->setRequired(true)
              ->addFilter('StringTrim')
              ->clearDecorators();

        $password = new Zend_Form_Element_Password("password");
        $password->setRequired(true)
                 ->addValidator('Alnum')
                 ->setRenderPassword(false)
                 ->addFilter('StringTrim');

        $form->addElements(array($login, $password));

        return $form;
    }

    protected function _getAddForm()
    {
        $form = new Zend_Form();

        $login = new Zend_Form_Element_Text('login');
        $login->setRequired(true)
              ->addFilter('Alnum')
              ->addFilter('StringTrim')
              ->addValidator(new App_Form_Validate_UniqueField())
              ->clearDecorators();

        $password = new Zend_Form_Element_Password("password");
        $password->setRequired(true)
                 ->addValidator('Alnum')
                 ->setRenderPassword(false)
                 ->addFilter('StringTrim');

        $role = new Zend_Form_Element_Radio("role");
        $role->setRequired(true)
             ->setMultiOptions(array('user' => 'Пользователь', 'admin' => 'Администратор'));

        $form->addElements(array($login, $password, $role));

        return $form;
    }

    protected function _getUpdateForm($user)
    {
        $form = new Zend_Form();

        $login = new Zend_Form_Element_Text('login');
        $login->setRequired(true)
             ->addFilter('Alnum')
             ->addFilter('StringTrim')
             ->addValidator(new App_Form_Validate_UniqueField('Model_User', 'login', $user['login']))
             ->clearDecorators();

        $password = new Zend_Form_Element_Password("password");
        $password->addValidator('Alnum')
                 ->setRenderPassword(false)
                 ->addFilter('StringTrim');

        $role = new Zend_Form_Element_Radio("role");
        $role->setRequired(true)
             ->setMultiOptions(array('user' => 'Пользователь', 'admin' => 'Администратор'));

        $form->addElements(array($login, $password, $role));

        return $form;
    }
}
