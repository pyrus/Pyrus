<?php
namespace Pyrus\Developer\CoverageAnalyzer\Web;
use Pyrus\Developer\CoverageAnalyzer\SourceFile;
class Summary extends \ArrayIterator
{
    public $sqlite;
    function __construct($sqlite)
    {
        $this->sqlite = $sqlite;
        parent::__construct($this->sqlite->retrievePaths());
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
        return new SourceFile($current, $this->sqlite, $this->sqlite->testpath, $this->sqlite->codepath, null, false);
    }
}