<?php
class Model_Message extends Zend_Db_Table_Abstract
{
    protected $_name = 'message';

    /**
     * Обновить данные сообщения
     */
    public function updateMessage($messageId, $data)
    {
        $where = $this->getAdapter()->quoteInto('id = ?', $messageId);
        $this->update($data, $where);
    }

    /**
     * Вернуть сообщение по id
     */
    public function getMessage($messageId)
    {
        $select = $this->select()->where('id = ?', $messageId);
        $result = $this->fetchRow($select);

        return $result;
    }

    /**
     * Вернуть все сообщения
     */
    public function getAll()
    {
        return $this->fetchAll($this->select()->order('id'));
    }

    /**
     * Удалить сообщение по id
     */
    public function deleteMessage($messageId)
    {
        $where = $this->getAdapter()->quoteInto('id = ?', $messageId);
        $this->delete($where);
    }
}
