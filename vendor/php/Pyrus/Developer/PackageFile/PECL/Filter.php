<?php
namespace Pyrus\Developer\PackageFile\PECL;
class Filter extends \FilterIterator
{
    protected $path;
    protected $role;
    function __construct($path, $it)
    {
        $this->path = $path;
        parent::__construct($it);
    }

    public function accept()
    {
    	if ($this->getInnerIterator()->isDot()) {
    	    return false;
    	}
    
    	$path = str_replace('\\', '/', $this->path);
        $path = str_replace($path, '', $this->getInnerIterator()->current()->getPathName());
        if ($path && $path[0] === DIRECTORY_SEPARATOR) {
            $path = substr($path, 1);
        }

        if (preg_match('@/?\.svn/@', $path)) {
        	return false;
        }
        if (preg_match('@/?\.cvsignore/@', $path)) {
        	return false;
        }
        if (preg_match('@/?CVS/@', $path)) {
        	return false;
        }
        if (preg_match('@\.tgz/@', $path)) {
        	return false;
        }
        if (preg_match('@\.tar/@', $path)) {
        	return false;
        }
        
        if (!$this->filterTestsDir()) {
            return false;
        }
        return $this->filterByCvsIgnore();
    }

    function filterByCvsIgnore()
    {
        static $cvsignorematches = array();
        $cvsignore = dirname($this->getInnerIterator()->current()->getPathName()) . '/.cvsignore';
        if (!file_exists($cvsignore)) {
            return true;
        }
        if (isset($cvsignorematches[$cvsignore])) {
            $tests = $cvsignorematches[$cvsignore];
        } else {
            $tests = array();
            foreach (new \SplFileObject($cvsignore) as $line) {
                if (!$line) {
                    continue;
                }
                $line = preg_quote($line, '@');
                $tests[] = str_replace(array('\\*', '\\?'), array('.*', '.'), $line);
            }
        }
        $path = $this->getInnerIterator()->current()->getPathName();
        foreach ($tests as $ignore) {
            if (preg_match("@/?$ignore@", $path)) {
                return false;
            }
        }
        return true;
    }

    function filterTestsDir()
    {
	if ($this->getInnerIterator()->current()->getBasename() == 'pear2coverage.db') {
	    return false;
	}
    	$invalid_extensions = array('diff','exp','log','out', 'xdebug');
	$info = pathinfo($this->getInnerIterator()->current()->getPathName());
	if (!isset($info['extension'])) {
	    return true;
	}
	return !in_array($info['extension'], $invalid_extensions);
    }
}
