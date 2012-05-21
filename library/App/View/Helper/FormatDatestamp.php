<?php
class App_View_Helper_FormatDatestamp extends Zend_View_Helper_Abstract
{
    public function formatDatestamp($timestamp, $format = Zend_Date::DATE_LONG)
    {
        try
        {
            $q = new Zend_Date($timestamp, null, 'ru_RU');
            return $q->toString($format);
        }
        catch (Exception $e)
        {
            return '';
        }
    }
}
