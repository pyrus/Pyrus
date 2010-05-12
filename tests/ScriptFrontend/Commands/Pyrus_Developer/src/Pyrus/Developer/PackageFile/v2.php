<?php
namespace PEAR2\Pyrus\Developer\PackageFile;
class v2 extends \PEAR2\Pyrus\PackageFile\v2
{
    function toArray($forpackaging = false)
    {
        if ($forpackaging) {
            return parent::toArray($forpackaging);
        }
        $a = parent::toArray();
        if (isset($a['package']['contents']['dir'])) {
            $a['package']['contents']['dir'] =
                $this->_recursiveXmlFilelist();
        }
        return $a;
    }

    function _recursiveXmlFilelist()
    {
        $dirs = array();
        foreach ($this->filelist as $file => $attribs) {
            $dirs = $this->_addDir($dirs, explode('/', dirname($file)), $file, $attribs);
        }
        $dirs = $this->_formatDir($dirs);
        $dirs = $this->_deFormat($dirs);
        $temp = $dirs;
        $dirs = array('attribs' => array('name' => '/'));
        if (isset($this->baseinstalldirs['/'])) {
            $dirs['attribs']['baseinstalldir'] = $this->baseinstalldirs['/'];
        } elseif (isset($this->baseinstalldirs[''])) {
            $dirs['attribs']['baseinstalldir'] = $this->baseinstalldirs[''];
        }
        foreach ($temp as $key => $info) {
            $dirs[$key] = $info;
        }
        return $dirs;
    }

    function _addDir($dirs, $dir, $file = null, $attributes = null)
    {
        if ($dir == array() || $dir == array('.')) {
            $attributes['attribs']['name'] = basename($file);
            $dirs['file'][basename($file)] = $attributes;
            return $dirs;
        }
        $curdir = array_shift($dir);
        if (!isset($dirs['dir'][$curdir])) {
            $dirs['dir'][$curdir] = array();
        }
        $dirs['dir'][$curdir] = $this->_addDir($dirs['dir'][$curdir], $dir, $file, $attributes);
        return $dirs;
    }

    function _formatDir($dirs)
    {
        if (!count($dirs)) {
            return array();
        }
        $newdirs = array();
        if (isset($dirs['attribs'])) {
            $newdirs['attribs'] = $dirs['attribs'];
        }
        if (isset($dirs['dir'])) {
            $newdirs['dir'] = $dirs['dir'];
        }
        if (isset($dirs['file'])) {
            $newdirs['file'] = $dirs['file'];
        }
        $dirs = $newdirs;
        if (isset($dirs['dir'])) {
            uksort($dirs['dir'], 'strnatcasecmp');
            foreach ($dirs['dir'] as $dir => $contents) {
                $dirs['dir'][$dir] = $this->_formatDir($dirs['dir'][$dir]);
            }
        }
        if (isset($dirs['file'])) {
            uksort($dirs['file'], 'strnatcasecmp');
        };
        return $dirs;
    }

    function _deFormat($dirs)
    {
        if (!count($dirs)) {
            return array();
        }
        $newdirs = array();
        if (isset($dirs['attribs'])) {
            $newdirs['attribs'] = $dirs['attribs'];
        }
        if (isset($dirs['dir'])) {
            foreach ($dirs['dir'] as $dir => $contents) {
                $newdir = array();
                $newdir['attribs']['name'] = $dir;
                if (isset($this->baseinstalldirs[$dir])) {
                    $newdir['attribs']['baseinstalldir'] = $this->baseinstalldirs[$dir];
                }
                $contents = $this->_deFormat($contents);
                foreach ($contents as $tag => $val) {
                    $newdir[$tag] = $val;
                }
                $newdirs['dir'][] = $newdir;
            }
            if (count($newdirs['dir']) == 1) {
                $newdirs['dir'] = $newdirs['dir'][0];
            }
        }
        if (isset($dirs['file'])) {
            foreach ($dirs['file'] as $name => $file) {
                $newdirs['file'][] = $file;
            }
            if (count($newdirs['file']) == 1) {
                $newdirs['file'] = $newdirs['file'][0];
            }
        }
        return $newdirs;
    }
}