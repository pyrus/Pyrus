<?php
namespace Pyrus\Developer\CoverageAnalyzer\Web;
use Pyrus\Developer\CoverageAnalyzer\SourceFile;

class Controller {
    protected $view;
    protected $sqlite;
    public $actionable;
    public static $rooturl;
    public $options = array('view' => 'toc');

    function __construct($options = array())
    {
        $this->options    = $options + $this->options;
        $this->actionable = $this->route();
    }

    function route()
    {
        if (isset($this->options['restart'])) {
            unset($_SESSION['fullpath']);
            unset($this->options['setdatabase']);
        }

        if (!isset($this->options['setdatabase'])
            && !isset($_SESSION['fullpath'])) {
            return new SelectDatabase;
        }

        if (!isset($this->options['setdatabase'])) {
            $this->options['setdatabase'] = $_SESSION['fullpath'];
        }

        $_SESSION['fullpath'] = $this->options['setdatabase'];

        if (!file_exists($this->options['setdatabase'])) {
            return new SelectDatabase;
        }

        $this->sqlite = new Aggregator($this->options['setdatabase']);

        if (isset($this->options['file'])) {
            if (isset($this->options['test'])) {
                $source = new SourceFile\PerTest($this->options['file'], $this->sqlite, $this->sqlite->testpath, $this->sqlite->codepath, $this->options['test']);
            } else {
                $source = new SourceFile($this->options['file'], $this->sqlite, $this->sqlite->testpath, $this->sqlite->codepath);
            }

            if (isset($this->options['line'])) {
                return new LineSummary($source, $this->options['line'], $this->sqlite->testpath);
            }

            return $source;
        }

        if (isset($this->options['test'])) {
            if ($this->options['test'] === 'TOC') {
                return new TestSummary($this->sqlite);
            }
            return new TestCoverage($this->sqlite, $this->options['test']);
        }

        if (isset($this->options['file'])) {
            if (isset($this->options['line'])) {
                return $this->view->fileLineTOC($this->sqlite, $this->options['file'], $this->options['line']);
            }
            return $this->view->fileCoverage($this->sqlite, $this->options['file']);
        }

        return new Summary($this->sqlite);
    }

    function getRootLink()
    {
        return self::$rooturl;
    }

    function getFileLink($file, $test = null, $line = null)
    {
        if ($line) {
            return self::$rooturl . '?file=' . urlencode($file) . '&line=' . $line;
        }
        if ($test) {
            return self::$rooturl . '?file=' . urlencode($file) . '&test=' . $test;
        }
        return self::$rooturl . '?file=' . urlencode($file);
    }

    function getTOCLink($test = false)
    {
        if ($test === true) {
            return self::$rooturl . '?test=TOC';
        }
        if ($test) {
            return self::$rooturl . '?test=' . urlencode($test);
        }
        return self::$rooturl;
    }

    function getLogoutLink()
    {
        return $this->rooturl . '?restart=1';
    }

    function getDatabase()
    {
        $this->sqlite = $this->view->getDatabase();
    }
}

