<?php
namespace Pyrus\Developer\CoverageAnalyzer\Web;
use Pyrus\Developer\CoverageAnalyzer\SourceFile;
class TestCoverage extends \ArrayIterator
{
    public $sqlite;
    public $test;

    function __construct($sqlite, $test)
    {
        $this->sqlite = $sqlite;
        $this->test   = $test;
        parent::__construct($this->sqlite->retrievePathsForTest($test));
    }

    function __call($method, $args)
    {
        return $this->sqlite->$method();
    }

    function __get($var)
    {
        return $this->sqlite->$var;
    }

    function current()
    {
        $current = parent::current();
        return new SourceFile\PerTest($current, $this->sqlite, $this->sqlite->testpath, $this->sqlite->codepath, $this->test);
    }

}