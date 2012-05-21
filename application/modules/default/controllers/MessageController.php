<?php
class MessageController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->layout()->active = 'message';
        parent::init();
    }

    public function indexAction()
    {
        $limit = 10;
        $page  = $this->_request->getParam("page", 1);

        $sort = $this->_request->getParam("sort", "id");
        $order = $this->_request->getParam("order", "asc");

        $modelUser = new Model_User();
        $currentUser = $modelUser->getCurrentUser();
        $filter = $this->_request->getParam("filter", "all");

        $modelMessage = new Model_Message();
        $select = $modelMessage->select();

        if ($filter != null)
        {
            switch ($filter)
            {
                case 'all':
                    $select = $select->where('`from` = ?', $currentUser['login'])->orWhere('`to` = ?', $currentUser['login']);
                    break;
                case 'from':
                    $select = $select->where('`from` = ?', $currentUser['login']);
                    break;
                case 'to':
                    $select = $select->where('`to` = ?', $currentUser['login']);
                    break;
            }
        }

        switch ($sort)
        {
            case 'id':
                $select = $select->order('id '.$order);
                break;
            case 'title':
                $select = $select->order('title '.$order);
                break;
            case 'from':
                $select = $select->order('from '.$order);
                break;
            case 'to':
                $select = $select->order('to '.$order);
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
        $this->view->messages = $paginator->getCurrentItems();
        $this->view->paginator = $paginator;
        $this->view->count = $paginator->getCurrentItemCount();
        $this->view->limit = $limit;
        $this->view->filter = $filter;
        $this->view->user = $currentUser;
    }

    public function addAction()
    {
        if ($this->_request->isPost())
        {
            $form = $this->_getAddForm();
            if ($form->isValid($this->_getAllParams()))
            {
                $modelUser = new Model_User();
                $currentUser = $modelUser->getCurrentUser();
                $data = array(
                    'title' => $form->getValue('title'),
                    'text' => $form->getValue('text'),
                    'from' => $currentUser['login'],
                    'to' => $form->getValue('to'),
                    'createDate' => new Zend_Db_Expr('NOW()'),
                );
                $modelMessage = new Model_Message();
                $modelMessage->insert($data);
                $this->_redirect("/message");
            }
            $this->view->data = $this->_getAllParams();
            $this->view->error = $form->getMessages();
        }
    }

    public function updateAction()
    {
        $messageId = $this->_request->getParam("messageId", null);
        if ($messageId == null)
        {
            throw new Zend_Controller_Action_Exception("Message not found", 404);
        }
        $modelMessage = new Model_Message();
        $message = $modelMessage->getMessage($messageId);

        $modelUser = new Model_User();
        $currentUser = $modelUser->getCurrentUser();
        if ($message['from'] != $currentUser['login'])
        {
            throw new Zend_Controller_Action_Exception("Hello hacker, how are you? =)", 404);
        }

        $this->view->message = $message;

        if ($this->_request->isPost())
        {
            $form = $this->_getUpdateForm($message);
            if ($form->isValid($this->_getAllParams()))
            {
                $data = array(
                    'title' => $form->getValue('title'),
                    'text' => $form->getValue('text'),
                    'to' => $form->getValue('to'),
                );
                $modelMessage->updateMessage($messageId, $data);
                $this->_redirect("/message");
            }
            $this->view->data = $this->_getAllParams();
            $this->view->error = $form->getMessages();
        }
    }

    public function viewAction()
    {
        $messageId = $this->_request->getParam("messageId", null);
        if ($messageId == null)
        {
            throw new Zend_Controller_Action_Exception("Message not found", 404);
        }
        $modelMessage = new Model_Message();
        $message = $modelMessage->getMessage($messageId);

        $modelUser = new Model_User();
        $currentUser = $modelUser->getCurrentUser();
        if ($message['to'] != $currentUser['login'] && $message['from'] != $currentUser['login'])
        {
            throw new Zend_Controller_Action_Exception("Hello hacker, how are you? =)", 404);
        }

        $this->view->message = $message;
    }

    public function deleteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $messageId = $this->getRequest()->getParam('messageId', null);
        if ($messageId != null)
        {
            $modelMessage = new Model_Message();
            $modelMessage->deleteMessage($messageId);
        }

        $this->_redirect("/message");
    }

    protected function _getAddForm()
    {
        $form = new Zend_Form();

        $title = new Zend_Form_Element_Text('title');
        $title->setRequired(true)
              ->addFilter('Alnum')
              ->addFilter('StringTrim')
              ->clearDecorators();

        $text = new Zend_Form_Element_Textarea('text');
        $text->setRequired(true)
             ->addFilter('StringTrim')
             ->clearDecorators();

        $to = new Zend_Form_Element_Text('to');
        $to->setRequired(true)
           ->addFilter('Alnum')
           ->addFilter('StringTrim')
           ->addValidator(new App_Form_Validate_ExistsValue())
           ->clearDecorators();

        $form->addElements(array($title, $text, $to));

        return $form;
    }

    protected function _getUpdateForm()
    {
        $form = new Zend_Form();

        $title = new Zend_Form_Element_Text('title');
        $title->setRequired(true)
              ->addFilter('Alnum')
              ->addFilter('StringTrim')
              ->clearDecorators();

        $text = new Zend_Form_Element_Textarea('text');
        $text->setRequired(true)
             ->addFilter('StringTrim')
             ->clearDecorators();

        $to = new Zend_Form_Element_Text('to');
        $to->setRequired(true)
           ->addFilter('Alnum')
           ->addFilter('StringTrim')
           ->addValidator(new App_Form_Validate_ExistsValue())
           ->clearDecorators();

        $form->addElements(array($title, $text, $to));

        return $form;
    }
}
