<?php
namespace PEAR2\Pyrus\Developer\PackageFile\PEAR2SVN;
class Filter extends \FilterIterator
{
    protected $path;
    protected $role;
    function __construct($path, $it, $role)
    {
        $this->path = $path;
        $this->role = $role;
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
        
        switch($this->role) {
        case 'test':
            return $this->filterTestsDir();
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
        if ($info['extension'] == 'php'
            && file_exists($info['dirname'].DIRECTORY_SEPARATOR.$info['filename'].'.phpt')) {
            // Assume this is the result of a failed .phpt test
            return false;
        }
        return !in_array($info['extension'], $invalid_extensions);
    }
}
