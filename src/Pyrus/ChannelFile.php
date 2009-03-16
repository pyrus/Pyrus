<?php
/**
 * PEAR2_Pyrus_ChannelFile
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * Base class for a PEAR2 package file
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_ChannelFile
{
    protected $info;
    public $path;
    
    function __construct($file)
    {
        $this->path = $file;
        $parser = new PEAR2_Pyrus_ChannelFile_Parser_v1;
        $data = file_get_contents($file);
        if ($data === false || empty($data)) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Unable to open channel xml file '
                . $file . ' or file was empty.');
        }
        $this->info = $parser->parse($data);
    }

    function __get($var)
    {
        return $this->info->$var;
    }
    
    function __set($var, $value)
    {
        $this->info->$var = $value;
    }
    
    function __call($func, $args)
    {
        // delegate to the internal object
        return call_user_func_array(array($this->info, $func), $args);
    }
    
    function __toString()
    {
        return $this->info->__toString();
    }
}
