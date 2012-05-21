<?php
class UserController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->layout()->active = 'user';
        parent::init();
    }

    public function indexAction()
    {

    }

    public function updateAction()
    {
        $modelUser = new Model_User();
        $user = $modelUser->getCurrentUser();
        $this->view->user = $user;

        if ($this->_request->isPost())
        {
            $form = $this->_getUpdateForm($user);
            if ($form->isValid($this->_getAllParams()))
            {
                $data = array();
                if ($form->getValue('login') != $user['login'])
                {
                    $data['login'] = $form->getValue('login');
                }
                if ($form->getValue('password'))
                {
                    $data['password'] = MD5($form->getValue('password'));
                }
                $modelUser->updateUser($user['id'], $data);
                $this->_redirect("/");
            }
            $this->view->data = $this->_getAllParams();
            $this->view->error = $form->getMessages();
        }
    }

    public function loginAction()
    {
        Zend_Layout::getMvcInstance()->setLayout("default/empty");

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
                    $this->_redirect("/");
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
        $this->_redirect("/");
    }

    public function autocompleteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $result = array();
        $q = $this->getRequest()->getParam('q', null);
        if ($q != null)
        {
            $modelUser = new Model_User();
            $users = $modelUser->fetchAll($modelUser->select()->where('login LIKE("?")', new Zend_Db_Expr('%' . $q . '%')));
            if (count($users))
            {
                foreach ($users as $user)
                {
                    $result[] = array(
                        'login' => $user['login'],
                    );
                }
            }
        }

        $this->_helper->json($result);
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

        $form->addElements(array($login, $password));

        return $form;
    }
}
