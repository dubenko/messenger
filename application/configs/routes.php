<?php
return array(
    'autocomplete' => new Zend_Controller_Router_Route('/autocomplete',
        array(
             'controller' => 'user',
             'action' => 'autocomplete',
        )),

    'login' => new Zend_Controller_Router_Route('/login',
        array(
             'controller' => 'user',
             'action' => 'login',
        )),

    'logout' => new Zend_Controller_Router_Route('/logout',
        array(
             'controller' => 'user',
             'action' => 'logout',
        )),
);
