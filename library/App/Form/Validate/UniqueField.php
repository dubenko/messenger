<?php

/**
 * App_Validate_EqualInputs
 *
 * Валидатор проверяет уникальность значения.
 */

class App_Form_Validate_UniqueField extends Zend_Validate_Abstract
{
    const NOT_UNIQUE = 'notUnique';

    protected $_messageTemplates = array(
        self::NOT_UNIQUE => 'Уже занято.'
    );

    protected $_modelName = 'Model_User';
    protected $_fieldName = 'login';
    protected $_excludeValue = null;

    /**
     * Sets validator options
     *
     * @param  string $modelName
     * @param  string $fieldName
     * @param  string $excludeValue
     * @return void
     */
    public function __construct($modelName = 'Model_User', $fieldName = 'login', $excludeValue = null)
    {
        $this->_modelName = $modelName;
        $this->_fieldName = $fieldName;
        if ($excludeValue != null)
        {
            $this->_excludeValue = $excludeValue;
        }
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

        if ($this->_excludeValue != null && $this->_excludeValue == $value)
        {
            return true;
        }

        $model = new $this->_modelName();
        $select = $model->select()->where($this->_fieldName.' = ?', $value);
        $result = $model->fetchRow($select);

        // Check to see if we got any results
        if (count($result) == 0)
        {
            return true;
        }

        $this->_error(self::NOT_UNIQUE);
        return false;
    }
}
