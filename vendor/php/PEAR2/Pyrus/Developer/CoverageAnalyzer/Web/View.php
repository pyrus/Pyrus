<?php
namespace PEAR2\Pyrus\Developer\CoverageAnalyzer\Web;
use PEAR2\Pyrus\Developer\CoverageAnalyzer\SourceFile;
/**
 * Takes a source file and outputs HTML source highlighting showing the
 * number of hits on each line, highlights un-executed lines in red
 */
class View
{
    protected $savePath;
    protected $testPath;
    protected $sourcePath;
    protected $source;
    protected $controller;

    function getDatabase()
    {
        $output = new \XMLWriter;
        if (!$output->openUri('php://output')) {
            throw new Exception('Cannot open output - this should never happen');
        }
        $output->startElement('html');
         $output->startElement('head');
          $output->writeElement('title', 'Enter a path to the database');
         $output->endElement();
         $output->startElement('body');
          $output->writeElement('h2', 'Please enter the path to a coverage database');
          $output->startElement('form');
           $output->writeAttribute('name', 'getdatabase');
           $output->writeAttribute('method', 'GET');
           $output->writeAttribute('action', $this->controller->getTOCLink());
           $output->startElement('input');
            $output->writeAttribute('size', '90');
            $output->writeAttribute('type', 'text');
            $output->writeAttribute('name', 'setdatabase');
           $output->endElement();
           $output->startElement('input');
            $output->writeAttribute('type', 'submit');
           $output->endElement();
          $output->endElement();
         $output->endElement();
        $output->endElement();
        $output->endDocument();
    }

    function setController($controller)
    {
        $this->controller = $controller;
    }

    function logoutLink(\XMLWriter $output)
    {
        $output->startElement('h5');
         $output->startElement('a');
          $output->writeAttribute('href', $this->controller->getLogoutLink());
          $output->text('Current database: ' . $_SESSION['fullpath'] . '.  Click to start over');
         $output->endElement();
        $output->endElement();
    }

    function TOC($sqlite)
    {
        $coverage = $sqlite->retrieveProjectCoverage();
        $this->renderSummary($sqlite, $sqlite->retrievePaths(), false, $coverage[1], $coverage[0], $coverage[2]);
    }

    function testTOC($sqlite, $test = null)
    {
        if ($test) {
            return $this->renderTestCoverage($sqlite, $test);
        }
        $this->renderTestSummary($sqlite);
    }

    function fileLineTOC($sqlite, $file, $line)
    {
        $source = new SourceFile($file, $sqlite, $sqlite->testpath, $sqlite->codepath);
        return $this->renderLineSummary($file, $line, $sqlite->testpath, $source->getLineLinks($line));
    }

    function fileCoverage($sqlite, $file, $test = null)
    {
        if ($test) {
            $source = new SourceFile\PerTest($file, $sqlite, $sqlite->testpath, $sqlite->codepath, $test);
        } else {
            $source = new SourceFile($file, $sqlite, $sqlite->testpath, $sqlite->codepath);
        }
        return $this->render($source, $test);
    }

    function mangleFile($path, $istest = false)
    {
        return $this->controller->getFileLink($path, $istest);
    }

    function mangleTestFile($path)
    {
        return $this->controller->getTOClink($path);
    }

    function getLineLink($name, $line)
    {
        return $this->controller->getFileLink($name, null, $line);
    }

    function renderLineSummary($name, $line, $testpath, $tests)
    {
        $output = new \XMLWriter;
        if (!$output->openUri('php://output')) {
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
        $this->logoutLink($output);
        $output->writeElement('h2', 'Tests covering line ' . $line . ' of ' . $name);
        $output->startElement('p');
        $output->startElement('a');
        $output->writeAttribute('href', $this->controller->getTOCLink());
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
        if (!$output->openUri('php://output')) {
            throw new Exception('Cannot render ' . $source->shortName() . ', opening XML failed');
        }
        $output->setIndent(false);
        $output->startElement('html');
        $output->text("\n ");
        $output->startElement('head');
        $output->text("\n  ");
        if ($istest) {
            $output->writeElement('title', 'Code Coverage for ' . $source->shortName() . ' in ' .
                                  str_replace($source->testpath() . DIRECTORY_SEPARATOR, '', $istest));
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
        $this->logoutLink($output);
        if ($istest) {
            $output->writeElement('h2', 'Code Coverage for ' . $source->shortName() . ' in ' .
                                  str_replace($source->testpath() . DIRECTORY_SEPARATOR, '', $istest));
        } else {
            $output->writeElement('h2', 'Code Coverage for ' . $source->shortName());
        }
        $output->text("\n ");
        $output->writeElement('h3', 'Coverage: ' . $source->coveragePercentage() . '% (Covered lines / Executable lines)');
        $info = $source->coverageInfo();
        $sourceCode = $source->source();

        $total = count($sourceCode);
        $output->writeRaw('<p><strong>' . $total . '</strong> total lines, of which <strong>' . $info[1] . '</strong> are executable, <strong>' . $info[2] .'</strong> are dead and <strong>' . ($total - $info[2] - $info[1]) . '</strong> are non-executable lines</p>');
        $output->writeRaw('<p>Of those <strong>' . $info[1] . '</strong> executable lines there are <strong>' . $info[0] . '</strong> lines covered with tests and <strong>' . ($info[1] - $info[0]) . '</strong> lack coverage</p>');
        $output->text("\n ");
        $output->startElement('p');
        $output->startElement('a');
        $output->writeAttribute('href', $this->controller->getTOCLink());
        $output->text('Aggregate Code Coverage for all tests');
        $output->endElement();
        $output->endElement();
        $output->startElement('pre');

        foreach ($sourceCode as $num => $line) {
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
            $cov = is_array($coverage) ? $coverage['coverage'] : $coverage;
            if ($cov === -2) {
                $output->writeAttribute('class', 'dead');
                $output->text('           ');
            } elseif ($cov < 1) {
                $output->writeAttribute('class', 'nc');
                $output->text('           ');
            } else {
                $output->writeAttribute('class', 'cv');
                if (!$istest) {
                    $output->startElement('a');
                    $output->writeAttribute('href', $this->getLineLink($source->name(), $num));
                }

                $text = is_string($coverage) ? $coverage : $coverage['link'];
                $output->text(str_pad($text, 10, ' ', STR_PAD_LEFT) . ' ');
                if (!$istest) {
                    $output->endElement();
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

    function renderSummary(Aggregator $agg, array $results, $istest = false, $total = 1, $covered = 1, $dead = 1)
    {
        $output = new \XMLWriter;
        if (!$output->openUri('php://output')) {
            throw new Exception('Cannot render test summary, opening XML failed');
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
            $output->writeElement('h2', 'Code Coverage Files');
            $output->writeElement('h3', 'Total lines: ' . $total . ', covered lines: ' . $covered . ', dead lines: ' . $dead);
            $percent = 0;
            if ($total > 0) {
                $percent = round(($covered / $total) * 100, 1);
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
        $this->logoutLink($output);
        $output->startElement('p');
        $output->startElement('a');
        $output->writeAttribute('href', $this->controller->getTOCLink(true));
        $output->text('Code Coverage per PHPT test');
        $output->endElement();
        $output->endElement();
        $output->startElement('ul');
        foreach ($results as $i => $name) {
            $output->flush();
            $source = new SourceFile($name, $agg, $agg->testpath, $agg->codepath, null, false);
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
        }
        $output->endElement();
        $output->endElement();
        $output->endDocument();
    }

    function renderTestSummary(Aggregator $agg)
    {
        $output = new \XMLWriter;
        if (!$output->openUri('php://output')) {
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
        $this->logoutLink($output);
        $output->writeElement('h2', 'Tests Executed, click for code coverage summary');
        $output->startElement('p');
        $output->startElement('a');
        $output->writeAttribute('href', $this->controller->getTOClink());
        $output->text('Aggregate Code Coverage for all tests');
        $output->endElement();
        $output->endElement();
        $output->startElement('ul');
        foreach ($agg->retrieveTestPaths() as $test) {
            $output->startElement('li');
            $output->startElement('a');
            $output->writeAttribute('href', $this->mangleTestFile($test));
            $output->text(str_replace($agg->testpath . '/', '', $test));
            $output->endElement();
            $output->endElement();
        }
        $output->endElement();
        $output->endElement();
        $output->endDocument();
    }

    function renderTestCoverage(Aggregator $agg, $test)
    {
        $reltest = str_replace($agg->testpath . '/', '', $test);
        $output = new \XMLWriter;
        if (!$output->openUri('php://output')) {
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
        $this->logoutLink($output);
        $output->writeElement('h2', 'Code Coverage Files for test ' . $reltest);
        $output->startElement('ul');
        $paths = $agg->retrievePathsForTest($test);
        foreach ($paths as $name) {
            $source = new SourceFile\PerTest($name, $agg, $agg->testpath, $agg->codepath, $test);
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
            $output->writeAttribute('href', $this->mangleFile($name, $test));
            $output->text($source->shortName());
            $output->endElement();
            $output->endElement();
        }
        $output->endElement();
        $output->endElement();
        $output->endDocument();
    }
}
