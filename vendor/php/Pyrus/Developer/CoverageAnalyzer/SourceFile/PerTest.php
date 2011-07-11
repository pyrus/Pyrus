<?php
namespace Pyrus\Developer\CoverageAnalyzer\SourceFile {
use Pyrus\Developer\CoverageAnalyzer\Aggregator,
    Pyrus\Developer\CoverageAnalyzer\AbstractSourceDecorator;
class PerTest extends \Pyrus\Developer\CoverageAnalyzer\SourceFile
{
    protected $testname;

    function __construct($path, Aggregator $agg, $testpath, $sourcepath, $testname, $coverage =  true)
    {
        $this->testname = $testname;
        parent::__construct($path, $agg, $testpath, $sourcepath, $coverage);
    }

    function setCoverage()
    {
        $this->coverage = $this->aggregator->retrieveCoverageByTest($this->path, $this->testname);
    }

    function coveredLines()
    {
        $info = $this->aggregator->coverageInfoByTest($this->path, $this->testname);
        return $info[0];
    }

    function render(AbstractSourceDecorator $decorator = null)
    {
        if ($decorator === null) {
            $decorator = new DefaultSourceDecorator('.');
        }
        return $decorator->render($this, $this->testname);
    }

    function coveragePercentage()
    {
        return $this->aggregator->coveragePercentage($this->path, $this->testname);
    }

    function coverageInfo()
    {
        return $this->aggregator->coverageInfoByTest($this->path, $this->testname);
    }
}
}
?>
