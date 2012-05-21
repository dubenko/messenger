<?php
class Model_User extends Zend_Db_Table_Abstract
{
    protected $_name = 'user';

    /**
     * Вернуть текущего пользователя
     */
    public function getCurrentUser()
    {
        $storage = Zend_Auth::getInstance()->getStorage();
        $user = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('default'))->getIdentity();
        Zend_Auth::getInstance()->setStorage($storage);

        if ($user == null)
        {
            return null;
        }
        $where = $this->getAdapter()->quoteInto("id = ?", $user->id);
        $userData = $this->fetchRow($where);

        return $userData;
    }

    /**
     * Вернуть текущего администратора
     */
    public function getCurrentAdmin()
    {
        $storage = Zend_Auth::getInstance()->getStorage();
        $admin = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('admin'))->getIdentity();
        Zend_Auth::getInstance()->setStorage($storage);

        if ($admin == null)
        {
            return null;
        }
        $where = $this->getAdapter()->quoteInto("id = ?", $admin->id);
        $userData = $this->fetchRow($where);

        return $userData;
    }

    /**
     * Обновить данные пользователя
     */
    public function updateUser($userId, $data)
    {
        $where = $this->getAdapter()->quoteInto('id = ?', $userId);
        $this->update($data, $where);
    }

    /**
     * Вернуть пользователя по id
     */
    public function getUser($userId)
    {
        $select = $this->select()->where('id = ?', $userId);
        $result = $this->fetchRow($select);

        return $result;
    }

    /**
     * Вернуть всех пользователей
     */
    public function getAll()
    {
        return $this->fetchAll($this->select()->order('id'));
    }

    /**
     * Удалить пользователя по id
     */
    public function deleteUser($userId)
    {
        $where = $this->getAdapter()->quoteInto('id = ?', $userId);
        $this->delete($where);
    }
}
