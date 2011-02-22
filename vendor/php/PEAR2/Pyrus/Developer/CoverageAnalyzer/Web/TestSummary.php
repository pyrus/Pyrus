<?php
namespace PEAR2\Pyrus\Developer\CoverageAnalyzer\Web;
use PEAR2\Pyrus\Developer\CoverageAnalyzer\SourceFile;
class TestSummary extends \ArrayIterator
{
    public $sqlite;
    function __construct($sqlite)
    {
        $this->sqlite = $sqlite;
        parent::__construct($this->sqlite->retrieveTestPaths());
    }

    function __call($method, $args)
    {
        return $this->sqlite->$method();
    }

    function __get($var)
    {
        return $this->sqlite->$var;
    }

}