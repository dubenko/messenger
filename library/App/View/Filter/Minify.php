<?php
class App_View_Filter_Minify
{
    public function filter($string)
    {
        return preg_replace(
            array('/>\s+/', '/\s+</', '/[\x0A\x0D]+/'),
            array('>', '<', ' '),
            $string
        );
    }
}
