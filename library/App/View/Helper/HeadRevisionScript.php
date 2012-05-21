<?php
class App_View_Helper_HeadRevisionScript extends Zend_View_Helper_HeadScript
{
    private static $cacheDir;
    private static $combine = 1;
    private static $compress = 1;
    private static $symlinks = array();

    private $_cache = array();

    public static function setConfig($cacheDir, $combine = 1, $compress = 1, $symlinks = array())
    {
        self::$cacheDir = rtrim($cacheDir, '/') . '/';
        self::$combine = $combine;
        self::$compress = $compress;
        self::$symlinks = $symlinks;
    }

    public function headRevisionScript()
    {
        return $this->toString();
    }

    public function searchJsFile($src)
    {
        $path = APPLICATION_ROOT . '/public' . $src;
        if (is_readable($path))
        {
            return $path;
        }

        foreach (self::$symlinks as $virtualPath => $realPath)
        {
            $path = str_replace($virtualPath, $realPath, "/$src");
            if (is_readable($path))
            {
                return $path;
            }
        }

        return false;
    }

    public function isCachable($item)
    {
        if (
            (isset($item->attributes['conditional'])
            && !empty($item->attributes['conditional'])
            && is_string($item->attributes['conditional']))
            || !isset($item->attributes['src'])
            || !$this->searchJsFile($item->attributes['src'])
        )
        {
            return false;
        }

        return true;
    }

    public function cache($item)
    {
        if (!empty($item->source))
        {
            $this->_cache[] = $item->source;
        }
        else
        {
            $filePath = $this->searchJsFile($item->attributes['src']);
            $this->_cache[] = array(
                'filepath' => $filePath,
                'mtime' => filemtime($filePath)
            );
        }
    }

    public function toString($indent = null)
    {
        $headScript = $this->view->headScript();

        $indent = (null !== $indent)
                ? $headScript->getWhitespace($indent)
                : $headScript->getIndent();

        $items = array();
        $headScript->getContainer()->ksort();
        foreach ($headScript as $item)
        {
            if (!$headScript->_isValid($item))
            {
                continue;
            }

            if (!$this->isCachable($item) || !self::$combine)
            {
                $items[] = $this->itemToString($item, $indent, '', '');
            }
            else
            {
                $this->cache($item);
            }
        }

        if (self::$combine)
        {
            $item = $this->getCompiledItem();
            if ($item != null)
            {
                array_unshift($items, $this->itemToString($item, $indent, '', ''));
            }
            else
            {
                foreach ($headScript as $item)
                {
                    if (!$headScript->_isValid($item))
                    {
                        continue;
                    }

                    $items[] = $this->itemToString($item, $indent, '', '');
                }
            }
        }
        $return = implode($headScript->getSeparator(), $items);

        return $return;
    }

    private function getCompiledItem()
    {
        $filename = md5(serialize($this->_cache));
        $path = self::$cacheDir . $filename . (self::$compress? '_compressed' : '') . '.js';
        if (!file_exists($path))
        {
            $compilerPath = APPLICATION_ROOT . '/data/compiler.jar';
            file_exists($compilerPath)
                || die('No compiler found.');
            $compilerString = 'java -jar ' . $compilerPath . (self::$compress ? ' --compilation_level SIMPLE_OPTIMIZATIONS ' : ' ');
            foreach ($this->_cache as $js)
            {
                if (file_exists($js['filepath']))
                {
                    $compilerString .= '--js ' . $js['filepath'] . ' ';
                }
                else
                {
                    die('File ' . $js['filepath'] . ' not found. Aborting.');
                }
            }
            $compilerString .= ' --js_output_file ' . $path;

            exec($compilerString);
            if (!file_exists($path))
            {
                return null;
            }
        }

        $url = str_replace(APPLICATION_ROOT . '/public', '', $path);
        $item = $this->createData('text/javascript', array('src' => $url));

        return $item;
    }
}
