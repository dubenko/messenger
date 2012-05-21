<?php
class App_View_Helper_HeadRevisionLink extends Zend_View_Helper_HeadLink
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

    public function headRevisionLink()
    {
        return $this->toString();
    }

    public function searchCssFile($href)
    {
        $path = APPLICATION_ROOT . '/public' . $href;
        if (is_readable($path))
        {
            return $path;
        }

        foreach (self::$symlinks as $virtualPath => $realPath)
        {
            $path = str_replace($virtualPath, $realPath, "/$href");
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
            (isset($item->conditionalStylesheet)
            && !empty($item->conditionalStylesheet)
            && is_string($item->conditionalStylesheet))
            || !isset($item->href)
            || !$this->searchCssFile($item->href)
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
            $filePath = $this->searchCssFile($item->href);
            $this->_cache[] = array(
                'filepath' => $filePath,
                'mtime' => filemtime($filePath)
            );
        }
    }

    public function toString($indent = null)
    {
        $headLink = $this->view->headLink();

        $items = array();
        $headLink->getContainer()->ksort();
        foreach ($headLink as $item)
        {
            if (!$headLink->_isValid($item))
            {
                continue;
            }

            if (!$this->isCachable($item) || !self::$combine)
            {
                $items[] = $this->itemToString($item);
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
                array_unshift($items, $this->itemToString($this->getCompiledItem()));
            }
            else
            {
                foreach ($headLink as $item)
                {
                    if (!$headLink->_isValid($item))
                    {
                        continue;
                    }

                    $items[] = $this->itemToString($item);
                }
            }
        }
        $return = implode($headLink->getSeparator(), $items);

        return $return;
    }

    private function getCompiledItem()
    {
        $filename = md5(serialize($this->_cache));
        $path = self::$cacheDir . $filename . (self::$compress ? '_compressed' : '') . '.css';
        if (!file_exists($path))
        {
            $notCompressedPath = self::$cacheDir . $filename . '.css';
            $cssContent = '';
            foreach ($this->_cache as $css)
            {
                $cssContent .= ' ' . file_get_contents($css['filepath']);
            }
            file_put_contents($notCompressedPath, $cssContent);

            if (self::$compress)
            {
                $compressorPath = APPLICATION_ROOT . '/data/yuicompressor-2.4.7.jar';
                file_exists($compressorPath)
                    || die('No compressor found.');

                exec('java -jar ' . $compressorPath . ' -v --type css --charset UTF-8 ' . $notCompressedPath . ' -o ' . $path);
                unlink($notCompressedPath);
                if (!file_exists($path))
                {
                    return null;
                }
            }
        }

        $url = str_replace(APPLICATION_ROOT . '/public', '', $path);
        $item = $this->createDataStylesheet(array('href' => $url));

        return $item;
    }
}
