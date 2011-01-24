<?php
namespace PEAR2\Pyrus\Developer\CoverageAnalyzer;
class SourceFile
{
    protected $source;
    protected $path;
    protected $sourcepath;
    protected $coverage;
    protected $aggregator;
    protected $testpath;
    protected $linelinks;

    function __construct($path, Aggregator $agg, $testpath, $sourcepath, $coverage = true)
    {
        $this->source = file($path);
        $this->path = $path;
        $this->sourcepath = $sourcepath;

        array_unshift($this->source, '');
        unset($this->source[0]); // make source array indexed by line number

        $this->aggregator = $agg;
        $this->testpath = $testpath;
        if ($coverage === true) {
            $this->setCoverage();
        }
    }

    function setCoverage()
    {
        $this->coverage = $this->aggregator->retrieveCoverage($this->path);
    }

    function aggregator()
    {
        return $this->aggregator;
    }

    function testpath()
    {
        return $this->testpath;
    }

    function render(AbstractSourceDecorator $decorator = null)
    {
        if ($decorator === null) {
            $decorator = new DefaultSourceDecorator('.');
        }
        return $decorator->render($this);
    }

    function coverage($line = null)
    {
        if ($line === null) {
            return $this->coverage;
        }

        if (!isset($this->coverage[$line])) {
            return false;
        }

        return $this->coverage[$line];
    }

    function coveragePercentage()
    {
        return $this->aggregator->coveragePercentage($this->path);
    }

    function coverageInfo()
    {
        return $this->aggregator->coverageInfo($this->path);
    }

    function name()
    {
        return $this->path;
    }

    function shortName()
    {
        return str_replace($this->sourcepath . DIRECTORY_SEPARATOR, '', $this->path);
    }

    function source()
    {
        $cov = $this->coverage();
        if (empty($cov)) {
            return $this->source;
        }

        /* Make sure we have as many lines as required
         * Sometimes Xdebug returns coverage on one line beyond what
         * our file has, this is PHP doing a return on the file.
         */
        $endLine = max(array_keys($cov));
        if (count($this->source) < $endLine) {
            // Add extra new line if required since we use <pre> to format
            $secondLast = $endLine - 1;
            $this->source[$secondLast] = str_replace("\r", '', $this->source[$secondLast]);
            $len = strlen($this->source[$secondLast]) - 1;
            if (substr($this->source[$secondLast], $len) != "\n") {
                $this->source[$secondLast] .= "\n";
            }

            $this->source[$endLine] = "\n";
        }

        return $this->source;
    }

    function coveredLines()
    {
        $info = $this->aggregator->coverageInfo($this->path);
        return $info[0];
    }

    function getLineLinks($line)
    {
        if (!isset($this->linelinks)) {
            $this->linelinks = $this->aggregator->retrieveLineLinks($this->path);
        }

        if (isset($this->linelinks[$line])) {
            return $this->linelinks[$line];
        }

        return false;
    }
}