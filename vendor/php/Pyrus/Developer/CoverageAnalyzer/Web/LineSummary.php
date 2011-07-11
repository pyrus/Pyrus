<?php
namespace Pyrus\Developer\CoverageAnalyzer\Web;
use Pyrus\Developer\CoverageAnalyzer\SourceFile;
class LineSummary extends \ArrayIterator
{
    public $source;
    public $line;

    function __construct($source, $line)
    {
        $this->source = $source;
        $this->line   = $line;
        parent::__construct($source->getLineLinks($this->line));
    }

    function __call($method, $args)
    {
        return $this->source->$method();
    }

    function __get($var)
    {
        return $this->source->$var;
    }
}