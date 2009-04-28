<?php
class test_Validator extends PEAR2_Pyrus_Dependency_Validator
{
    public $os = 'linux';
    public $sysname = 'linux';
    public $patterns = array();
    public $extensions = array();
    public $versions = array();
    public $phpversion = '5.3.0';
    public $pearversion = '2.0.0';

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function getPHP_OS()
    {
        return $this->os;
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function getsysname()
    {
        return $this->sysname;
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function matchSignature($pattern)
    {
        if (isset($this->patterns[$pattern])) {
            return true;
        }
        return false;
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function extension_loaded($name)
    {
        return isset($this->extensions[$name]);
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function phpversion($name = null)
    {
        if ($name !== null) {
            if (!isset($this->versions[$name])) {
                return null;
            }
            return $this->versions[$name];
        } else {
            return $this->phpversion;
        }
    }

    /**
     * This makes unit-testing a heck of a lot easier
     */
    function getPEARVersion()
    {
        return $this->pearversion;
    }
}