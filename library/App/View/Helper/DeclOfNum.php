<?php
class App_View_Helper_DeclOfNum extends Zend_View_Helper_Abstract
{
    public function declOfNum($number, $titles)
    {
        $number = abs($number);
        $cases = array (2, 0, 1, 1, 1, 2);
        return $number." ".$titles[ ($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)] ];
    }
}
