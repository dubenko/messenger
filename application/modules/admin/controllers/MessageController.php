<?php
class Admin_MessageController extends Zend_Controller_Action
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
        $this->view->headScript()->appendFile('/js/admin/message.js');

        $limit = 10;
        $page  = $this->_request->getParam("page", 1);

        $sort = $this->_request->getParam("sort", "id");
        $order = $this->_request->getParam("order", "asc");

        $filter = $this->_request->getParam("filter", null);
        $filterUri = array();

        $modelMessage = new Model_Message();
        $select = $modelMessage->select();

        if ($filter != null)
        {
            foreach ($filter as $key => $value)
            {
                if ($value == '')
                {
                    continue;
                }

                switch ($key)
                {
                    case 'from':
                    case 'to':
                        $select = $select->where('`'.$key.'` = ?', $value);
                        break;
                }

                if ($value != '')
                {
                    $filterUri[] = "filter[".$key."]=".urlencode($value);
                }
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
        $this->view->filterUri = $filterUri;
    }

    public function addAction()
    {
        $this->view->headScript()->appendFile('/js/admin/message.js');
        if ($this->_request->isPost())
        {
            $form = $this->_getAddForm();
            if ($form->isValid($this->_getAllParams()))
            {
                $data = array(
                    'title' => $form->getValue('title'),
                    'text' => $form->getValue('text'),
                    'from' => $form->getValue('from'),
                    'to' => $form->getValue('to'),
                    'createDate' => new Zend_Db_Expr('NOW()'),
                );
                $modelMessage = new Model_Message();
                $modelMessage->insert($data);
                $this->_redirect("/admin/message");
            }
            $this->view->data = $this->_getAllParams();
            $this->view->error = $form->getMessages();
        }
    }

    public function updateAction()
    {
        $this->view->headScript()->appendFile('/js/admin/message.js');
        $messageId = $this->_request->getParam("messageId", null);
        if ($messageId == null)
        {
            throw new Zend_Controller_Action_Exception("Message not found", 404);
        }
        $modelMessage = new Model_Message();
        $message = $modelMessage->getMessage($messageId);
        $this->view->message = $message;

        if ($this->_request->isPost())
        {
            $form = $this->_getUpdateForm($message);
            if ($form->isValid($this->_getAllParams()))
            {
                $data = array(
                    'title' => $form->getValue('title'),
                    'text' => $form->getValue('text'),
                    'from' => $form->getValue('from'),
                    'to' => $form->getValue('to'),
                );
                $modelMessage->updateMessage($messageId, $data);
                $this->_redirect("/admin/message");
            }
            $this->view->data = $this->_getAllParams();
            $this->view->error = $form->getMessages();
        }
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

        $this->_redirect("/admin/message");
    }

    public function filterAction()
    {
        $this->_helper->layout()->disableLayout();

        $filter = $this->_request->getParam("filter", array());
        $filter = array_merge(array('from' => '', 'to' => ''), $filter);

        $this->view->filter = $filter;
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
             ->addFilter('Alnum')
             ->addFilter('StringTrim')
             ->clearDecorators();

        $from = new Zend_Form_Element_Text('from');
        $from->setRequired(true)
             ->addFilter('Alnum')
             ->addFilter('StringTrim')
             ->addValidator(new App_Form_Validate_ExistsValue())
             ->clearDecorators();

        $to = new Zend_Form_Element_Text('to');
        $to->setRequired(true)
           ->addFilter('Alnum')
           ->addFilter('StringTrim')
           ->addValidator(new App_Form_Validate_ExistsValue())
           ->clearDecorators();

        $form->addElements(array($title, $text, $from, $to));

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
             ->addFilter('Alnum')
             ->addFilter('StringTrim')
             ->clearDecorators();

        $from = new Zend_Form_Element_Text('from');
        $from->setRequired(true)
             ->addFilter('Alnum')
             ->addFilter('StringTrim')
             ->addValidator(new App_Form_Validate_ExistsValue())
             ->clearDecorators();

        $to = new Zend_Form_Element_Text('to');
        $to->setRequired(true)
           ->addFilter('Alnum')
           ->addFilter('StringTrim')
           ->addValidator(new App_Form_Validate_ExistsValue())
           ->clearDecorators();

        $form->addElements(array($title, $text, $from, $to));

        return $form;
    }
}
