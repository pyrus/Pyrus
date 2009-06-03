<?php
namespace PEAR2\Pyrus\Developer\CoverageAnalyzer {
abstract class AbstractSourceDecorator
{
    abstract function render(SourceFile $source);
    abstract function renderSummary(Aggregator $agg, array $results, $basePath, $istest = false, $total = 1, $covered = 1);
    abstract function renderTestCoverage(Aggregator $agg, $testpath, $basePath);
}
}
?>
