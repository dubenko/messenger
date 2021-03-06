<?php
class App_View_Helper_FormDate extends Zend_View_Helper_FormElement
{
    public function formDate ($name, $value = null, $attribs = null)
    {
        $day = '';
        $month = '';
        $year = '';
        if (is_array($value))
        {
            $day = $value['day'];
            $month = $value['month'];
            $year = $value['year'];
        }
        elseif (strtotime($value))
        {
            list ($year, $month, $day) = explode('-', date('Y-m-d', strtotime($value)));
        }

        $dayAttribs = isset($attribs['dayAttribs']) ? $attribs['dayAttribs'] : array();
        $monthAttribs = isset($attribs['monthAttribs']) ? $attribs['monthAttribs'] : array();
        $yearAttribs = isset($attribs['yearAttribs']) ? $attribs['yearAttribs'] : array();

        $dayMultiOptions = array('' => '');
        for ($i = 1; $i < 32; $i ++)
        {
            $index = str_pad($i, 2, '0', STR_PAD_LEFT);
            $dayMultiOptions[$index] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        $monthMultiOptions = array('' => '');
        for ($i = 1; $i < 13; $i ++)
        {
            $index = str_pad($i, 2, '0', STR_PAD_LEFT);
            $monthMultiOptions[$index] = date('F', mktime(null, null, null, $i, 01));
        }

        $startYear = date('Y');
        if (isset($attribs['startYear']))
        {
            $startYear = $attribs['startYear'];
            unset($attribs['startYear']);
        }

        $stopYear = $startYear - 60;
        if (isset($attribs['stopYear']))
        {
            $stopYear = $attribs['stopYear'];
            unset($attribs['stopYear']);
        }

        $yearMultiOptions = array('' => '');
        if ($stopYear < $startYear)
        {
            for ($i = $startYear; $i >= $stopYear; $i--)
            {
                $yearMultiOptions[$i] = $i;
            }
        }
        else
        {
            for ($i = $startYear; $i <= $stopYear; $i++)
            {
                $yearMultiOptions[$i] = $i;
            }
        }

        // возвращает 3 селекта, разделённых &nbsp;
        return
            $this->view->formSelect(
                $name . '[day]',
                $day,
                $dayAttribs,
                $dayMultiOptions) . '&nbsp;' .
            $this->view->formSelect(
                $name . '[month]',
                $month,
                $monthAttribs,
                $monthMultiOptions) . '&nbsp;' .
            $this->view->formSelect(
                $name . '[year]',
                $year,
                $yearAttribs,
                $yearMultiOptions
            );
    }
}
