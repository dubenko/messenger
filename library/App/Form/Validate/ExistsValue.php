<?php

/**
 * App_Validate_ExistsValue
 *
 * Валидатор проверяет уникальность значения.
 */

class App_Form_Validate_ExistsValue extends Zend_Validate_Abstract
{
    const NOT_EXISTS = 'notExists';

    protected $_messageTemplates = array(
        self::NOT_EXISTS => 'Такого нет.'
    );

    protected $_modelName = 'Model_User';
    protected $_fieldName = 'login';

    /**
     * Sets validator options
     *
     * @param  string $modelName
     * @param  string $fieldName
     * @return void
     */
    public function __construct($modelName = 'Model_User', $fieldName = 'login')
    {
        $this->_modelName = $modelName;
        $this->_fieldName = $fieldName;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the value is found to NOT have a match in the fieldname in the model specified
     *
     * @param Array $value
     * @param Array $request
     * @return boolean
     */
    public function isValid($value, $request = null)
    {
        // Set the value
        $value = (string) $value;
        $this->_setValue($value);

        $model = new $this->_modelName();
        $select = $model->select()->where($this->_fieldName.' = ?', $value);
        $result = $model->fetchRow($select);

        // Check to see if we got any results
        if (count($result) > 0)
        {
            return true;
        }

        $this->_error(self::NOT_EXISTS);
        return false;
    }
}
