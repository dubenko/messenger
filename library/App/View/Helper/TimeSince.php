<?php
class App_View_Helper_TimeSince extends Zend_View_Helper_Abstract
{
    public function timeSince($time)
    {
        if ($time != 0)
        {
            $dateDiff = time() - $time;
            $limits = array(
                array('limit' => 0, 'text' => 'Меньше часа'),
                array('limit' => 60*60, 'text' => array('час', 'часа', 'часов')),
                array('limit' => 60*60*24, 'text' => array('день', 'дня', 'дней')),
                array('limit' => 60*60*24*30, 'text' => array('месяц', 'месяца', 'месяцев')),
                array('limit' => 60*60*24*365, 'text' => array('год', 'года', 'лет')),
            );
            $result = '';
            $lastIndex = sizeof($limits) - 1;
            for ($i = 0; ($result == '') && ($i <= $lastIndex); $i++)
            {
                if (($i == $lastIndex) || (($dateDiff >= $limits[$i]['limit']) && ($dateDiff < $limits[$i + 1]['limit'])))
                {
                    if (is_array($limits[$i]['text']))
                    {
                        $helper = new App_View_Helper_DeclOfNum();
                        $result = $helper->declOfNum(floor($dateDiff / $limits[$i]['limit']), $limits[$i]['text']);
                    }
                    else
                    {
                        $result = $limits[$i]['text'];
                    }
                }
            }
        }
        else
        {
            $result = false;
        }

           return $result;
    }
}
