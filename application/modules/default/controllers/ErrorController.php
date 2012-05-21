<?php
class ErrorController extends Zend_Controller_Action
{
    public function init()
    {
        parent::init();
        $this->_helper->layout->setLayout('default/error');
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        if (isset($errors->type))
        {
            switch ($errors->type)
            {
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                    // 404 error -- controller or action not found
                    $this->getResponse()->setHttpResponseCode(404);
                    $this->view->message = 'Page not found';
                    $this->view->error   = 404;
                    break;
                default:
                    // application error
                    $this->getResponse()->setHttpResponseCode(500);
                    $this->view->message = "Application error";
                    $this->view->error   = $errors->exception->getCode();
                    break;
            }

            // conditionally display exceptions
//            if ($this->getInvokeArg('displayExceptions') == true)
//            {
                $this->view->exception = Zend_Debug::dump($errors->exception);
//            }
        }
        else
        {
            throw new Zend_Controller_Action_Exception("Not Found", 404);
        }
    }

    public function denyAction()
    {

    }
}
