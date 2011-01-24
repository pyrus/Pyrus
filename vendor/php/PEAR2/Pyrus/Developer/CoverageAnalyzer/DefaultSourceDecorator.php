<?php
namespace PEAR2\Pyrus\Developer\CoverageAnalyzer {
/**
 * Takes a source file and outputs HTML source highlighting showing the
 * number of hits on each line, highlights un-executed lines in red
 */
class DefaultSourceDecorator extends AbstractSourceDecorator
{
    protected $savePath;
    protected $testPath;
    protected $sourcePath;
    protected $source;

    function __construct($savePath, $testPath, $sourcePath)
    {
        if (!$savePath || !file_exists($savePath) || !is_dir($savePath)) {
            throw new Exception('Invalid save path for default renderer');
        }
        $this->testPath = $testPath;
        $this->sourcePath = $sourcePath;
        $this->savePath = realpath($savePath);
        file_put_contents($this->savePath . '/cover.css', '.ln {background-color:yellow;}
.cv {background-color:#CAD7FE;}
.nc {background-color:red;}
.bad {background-color:red;white-space:pre;font-family:courier;}
.ok {background-color:yellow;white-space:pre;font-family:courier;}
.good {background-color:green;white-space:pre;font-family:courier;}
.dead {background-color:orange;white-space:pre;font-family:courier;}');
    }

    function manglePath($path, $istest = false)
    {
        return $this->savePath . '/' . $this->mangleFile($path, $istest);
    }

    function mangleFile($path, $istest = false)
    {
        $path = substr($path, strlen($this->sourcePath) + 1);
        if ($istest) {
            $istest = str_replace($this->testPath . DIRECTORY_SEPARATOR, '', $istest);
            return 'cov-test-' . str_replace(array('/', '\\'), array('@','@'), $istest) . '-' .
                    str_replace(array('/', '\\'), array('@','@'), $path) . '.html';
        }
        return 'cov-' . str_replace(array('/', '\\'), array('@','@'), $path) . '.html';
    }

    function getLinePath($name, $line)
    {
        return $this->savePath . '/' . $this->getLineLink($name, $line);
    }

    function mangleTestFile($path)
    {
        $path = substr($path, strlen($this->testPath));
        return 'test-' . str_replace(array('/', '\\'), array('@','@'), $path) . '.html';
    }

    function mangleTestPath($path)
    {
        return $this->savePath . '/' . $this->mangleTestFile($path);
    }

    function getLineLink($name, $line)
    {
        return 'line-' . $line . '-' . $this->mangleFile($name);
    }

    function renderLineSummary($name, $line, $testpath, $tests)
    {
        $output = new \XMLWriter;
        if (!$output->openUri($this->getLinePath($name, $line))) {
            throw new Exception('Cannot render ' . $name . ' line ' . $line . ', opening XML failed');
        }
        $output->setIndentString(' ');
        $output->setIndent(true);
        $output->startElement('html');
        $output->startElement('head');
        $output->writeElement('title', 'Tests covering line ' . $line . ' of ' . $name);
        $output->startElement('link');
        $output->writeAttribute('href', 'cover.css');
        $output->writeAttribute('rel', 'stylesheet');
        $output->writeAttribute('type', 'text/css');
        $output->endElement();
        $output->endElement();
        $output->startElement('body');
        $output->writeElement('h2', 'Tests covering line ' . $line . ' of ' . $name);
        $output->startElement('p');
        $output->startElement('a');
        $output->writeAttribute('href', 'index.html');
        $output->text('Aggregate Code Coverage for all tests');
        $output->endElement();
        $output->endElement();
        $output->startElement('p');
        $output->startElement('a');
        $output->writeAttribute('href', $this->mangleFile($name));
        $output->text('File ' . $name . ' code coverage');
        $output->endElement();
        $output->endElement();
        $output->startElement('ul');
        foreach ($tests as $testfile) {
            $output->startElement('li');
            $output->startElement('a');
            $output->writeAttribute('href', $this->mangleTestFile($testfile));
            $output->text(str_replace($testpath . '/', '', $testfile));
            $output->endElement();
            $output->endElement();
        }
        $output->endElement();
        $output->endElement();
        $output->endDocument();
    }

    /**
     * @param PEAR2\Pyrus\Developer\CodeCoverage\SourceFile $source
     * @param string $istest path to test file this is covering, or false for aggregate
     */
    function render(SourceFile $source, $istest = false)
    {
        $output = new \XMLWriter;
        if (!$output->openUri($this->manglePath($source->name(), $istest))) {
            throw new Exception('Cannot render ' . $source->name() . ', opening XML failed');
        }
        $output->setIndent(false);
        $output->startElement('html');
        $output->text("\n ");
        $output->startElement('head');
        $output->text("\n  ");
        if ($istest) {
            $output->writeElement('title', 'Code Coverage for ' . $source->shortName() . ' in ' . $istest);
        } else {
            $output->writeElement('title', 'Code Coverage for ' . $source->shortName());
        }
        $output->text("\n  ");
        $output->startElement('link');
        $output->writeAttribute('href', 'cover.css');
        $output->writeAttribute('rel', 'stylesheet');
        $output->writeAttribute('type', 'text/css');
        $output->endElement();
        $output->text("\n  ");
        $output->endElement();
        $output->text("\n ");
        $output->startElement('body');
        $output->text("\n ");
        if ($istest) {
            $output->writeElement('h2', 'Code Coverage for ' . $source->shortName() . ' in ' . $istest);
        } else {
            $output->writeElement('h2', 'Code Coverage for ' . $source->shortName());
        }
        $output->text("\n ");
        $output->writeElement('h3', 'Coverage: ' . $source->coveragePercentage() . '%');
        $output->text("\n ");
        $output->startElement('p');
        $output->startElement('a');
        $output->writeAttribute('href', 'index.html');
        $output->text('Aggregate Code Coverage for all tests');
        $output->endElement();
        $output->endElement();
        $output->startElement('pre');
        foreach ($source->source() as $num => $line) {
            $coverage = $source->coverage($num);

            $output->startElement('span');
            $output->writeAttribute('class', 'ln');
            $output->text(str_pad($num, 8, ' ', STR_PAD_LEFT));
            $output->endElement();

            if ($coverage === false) {
                $output->text(str_pad(': ', 13, ' ', STR_PAD_LEFT) . $line);
                continue;
            }

            $output->startElement('span');
            if ($coverage < 1) {
                $output->writeAttribute('class', 'nc');
                $output->text('           ');
            } else {
                $output->writeAttribute('class', 'cv');
                if (!$istest) {
                    $output->startElement('a');
                    $output->writeAttribute('href', $this->getLineLink($source->name(), $num));
                }
                $output->text(str_pad($coverage, 10, ' ', STR_PAD_LEFT) . ' ');
                if (!$istest) {
                    $output->endElement();
                    $this->renderLineSummary($source->name(), $num, $source->testpath(),
                                             $source->getLineLinks($num));
                }
            }

            $output->text(': ' .  $line);
            $output->endElement();
        }
        $output->endElement();
        $output->text("\n ");
        $output->endElement();
        $output->text("\n ");
        $output->endElement();
        $output->endDocument();
    }

    function renderSummary(Aggregator $agg, array $results, $basePath, $istest = false, $total = 1, $covered = 1, $dead = 1)
    {
        $output = new \XMLWriter;
        if ($istest) {
            if (!$output->openUri($this->savePath . '/index-' . str_replace($istest, '/', '@') . '.html')) {
                throw new Exception('Cannot render test  ' . $istest . ' summary, opening XML failed');
            }
        } else {
            if (!$output->openUri($this->savePath . '/index.html')) {
                throw new Exception('Cannot render test summary, opening XML failed');
            }
        }
        $output->setIndentString(' ');
        $output->setIndent(true);
        $output->startElement('html');
        $output->startElement('head');
        if ($istest) {
            $output->writeElement('title', 'Code Coverage Summary [' . $istest . ']');
        } else {
            $output->writeElement('title', 'Code Coverage Summary');
        }
        $output->startElement('link');
        $output->writeAttribute('href', 'cover.css');
        $output->writeAttribute('rel', 'stylesheet');
        $output->writeAttribute('type', 'text/css');
        $output->endElement();
        $output->endElement();
        $output->startElement('body');
        if ($istest) {
            $output->writeElement('h2', 'Code Coverage Files for test ' . $istest);
        } else {
            $output->writeElement('h2', 'Code Coverage Files for ' . $basePath);
            $output->writeElement('h3', 'Total lines: ' . $total . ', covered lines: ' . $covered . ', dead lines: ' . $dead);
            $percent = 0;
            if ($total > 0) {
                $percent = round(($covered / $total) * 100);
            }
            $output->startElement('p');
            if ($percent < 50) {
                $output->writeAttribute('class', 'bad');
            } elseif ($percent < 75) {
                $output->writeAttribute('class', 'ok');
            } else {
                $output->writeAttribute('class', 'good');
            }
            $output->text($percent . '% code coverage');
            $output->endElement();
        }
        $output->startElement('p');
        $output->startElement('a');
        $output->writeAttribute('href', 'index-test.html');
        $output->text('Code Coverage per PHPT test');
        $output->endElement();
        $output->endElement();
        $output->startElement('ul');
        echo "[Step 1 of 2] Rendering files\n";
        foreach ($results as $i => $name) {
            echo '(' . ($i+1) . ' of ' . count($results) . ') ' . $name . "\n";
            $source = new SourceFile($name, $agg, $this->testPath, $this->sourcePath);
            $output->startElement('li');
            $percent = $source->coveragePercentage();
            $output->startElement('div');
            if ($percent < 50) {
                $output->writeAttribute('class', 'bad');
            } elseif ($percent < 75) {
                $output->writeAttribute('class', 'ok');
            } else {
                $output->writeAttribute('class', 'good');
            }
            $output->text(' Coverage: ' . str_pad($percent . '%', 4, ' ', STR_PAD_LEFT));
            $output->endElement();
            $output->startElement('a');
            $output->writeAttribute('href', $this->mangleFile($name, $istest));
            $output->text($source->shortName());
            $output->endElement();
            $output->endElement();
            $source->render($this);
        }
        echo "done\n";
        $output->endElement();
        $output->endElement();
        $output->endDocument();
    }

    function renderTestSummary(Aggregator $agg, $testpath)
    {
        $output = new \XMLWriter;
        if (!$output->openUri($this->savePath . '/index-test.html')) {
                throw new Exception('Cannot render tests summary, opening XML failed');
        }
        $output->setIndentString(' ');
        $output->setIndent(true);
        $output->startElement('html');
        $output->startElement('head');
        $output->writeElement('title', 'Test Summary');
        $output->startElement('link');
        $output->writeAttribute('href', 'cover.css');
        $output->writeAttribute('rel', 'stylesheet');
        $output->writeAttribute('type', 'text/css');
        $output->endElement();
        $output->endElement();
        $output->startElement('body');
        $output->writeElement('h2', 'Tests Executed, click for code coverage summary');
        $output->startElement('p');
        $output->startElement('a');
        $output->writeAttribute('href', 'index.html');
        $output->text('Aggregate Code Coverage for all tests');
        $output->endElement();
        $output->endElement();
        $output->startElement('ul');
        foreach ($agg->retrieveTestPaths() as $test) {
            $output->startElement('li');
            $output->startElement('a');
            $output->writeAttribute('href', $this->mangleTestFile($test));
            $output->text(str_replace($testpath . '/', '', $test));
            $output->endElement();
            $output->endElement();
        }
        $output->endElement();
        $output->endElement();
        $output->endDocument();
    }

    function renderTestCoverage(Aggregator $agg, $testpath, $basePath)
    {
        $this->renderTestSummary($agg, $testpath);
        $testpaths = $agg->retrieveTestPaths();
        echo "Rendering test files\n";
        foreach ($testpaths as $i => $test) {
            echo '(', $i+1, ' of ', count($testpaths) . ') ', $test;
            $reltest = str_replace($testpath . '/', '', $test);
            $output = new \XMLWriter;
            if (!$output->openUri($this->mangleTestPath($test))) {
                throw new Exception('Cannot render test ' . $reltest . ' coverage, opening XML failed');
            }
            $output->setIndentString(' ');
            $output->setIndent(true);
            $output->startElement('html');
            $output->startElement('head');
            $output->writeElement('title', 'Code Coverage Summary for test ' . $reltest);
            $output->startElement('link');
            $output->writeAttribute('href', 'cover.css');
            $output->writeAttribute('rel', 'stylesheet');
            $output->writeAttribute('type', 'text/css');
            $output->endElement();
            $output->endElement();
            $output->startElement('body');
            $output->writeElement('h2', 'Code Coverage Files for test ' . $reltest);
            $output->startElement('ul');
            $paths = $agg->retrievePathsForTest($test);
            foreach ($paths as $name) {
                echo '.';
                $source = new SourceFile\PerTest($name, $agg, $testpath, $basePath, $test);
                $this->render($source, $reltest);
                $output->startElement('li');
                $percent = $source->coveragePercentage();
                $output->startElement('div');
                if ($percent < 50) {
                    $output->writeAttribute('class', 'bad');
                } elseif ($percent < 75) {
                    $output->writeAttribute('class', 'ok');
                } else {
                    $output->writeAttribute('class', 'good');
                }
                $output->text(' Coverage: ' . str_pad($source->coveragePercentage() . '%', 4, ' ', STR_PAD_LEFT));
                $output->endElement();
                $output->startElement('a');
                $output->writeAttribute('href', $this->mangleFile($name, $reltest));
                $output->text($source->shortName());
                $output->endElement();
                $output->endElement();
            }
            echo "done\n";
            $output->endElement();
            $output->endElement();
            $output->endDocument();
        }
        echo "done\n";
    }
}
}
?>
