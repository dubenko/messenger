<?php
require_once 'zendLoader.php';
$data = array(
    'login' => 'admin',
    'password' => MD5('ghbdtn'),
    'role' => 'admin',
    'createDate' => new Zend_Db_Expr('NOW()'),
);
$modelUser = new Model_User();
$modelUser->insert($data);
