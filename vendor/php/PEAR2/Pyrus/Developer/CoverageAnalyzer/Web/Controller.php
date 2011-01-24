<?php
namespace PEAR2\Pyrus\Developer\CoverageAnalyzer\Web {
class Controller {
    protected $view;
    protected $sqlite;
    protected $rooturl;

    function __construct(View $view, $rooturl)
    {
        $this->view = $view;
        $view->setController($this);
        $this->rooturl = $rooturl;
    }

    function route()
    {
        if (!isset($_SESSION['fullpath']) || isset($_GET['restart'])) {
            unset($_SESSION['fullpath']);
            if (isset($_GET['setdatabase'])) {
                if (file_exists($_GET['setdatabase'])) {
                    try {
                        $this->sqlite = new Aggregator($_GET['setdatabase']);
                        $_SESSION['fullpath'] = $_GET['setdatabase'];
                        return $this->view->TOC($this->sqlite);
                    } catch (\Exception $e) {
                        echo $e->getMessage() . '<br />';
                        // fall through
                    }
                }
            }
            return $this->getDatabase();
        } else {
            try {
                $this->sqlite = new Aggregator($_SESSION['fullpath']);
                if (isset($_GET['test'])) {
                    if ($_GET['test'] === 'TOC') {
                        return $this->view->testTOC($this->sqlite);
                    }
                    if (isset($_GET['file'])) {
                        return $this->view->fileCoverage($this->sqlite, $_GET['file'], $_GET['test']);
                    }
                    return $this->view->testTOC($this->sqlite, $_GET['test']);
                }
                if (isset($_GET['file'])) {
                    if (isset($_GET['line'])) {
                        return $this->view->fileLineTOC($this->sqlite, $_GET['file'], $_GET['line']);
                    }
                    return $this->view->fileCoverage($this->sqlite, $_GET['file']);
                }
            } catch (\Exception $e) {
                echo $e->getMessage() . '<br \>';
            }
            return $this->view->TOC($this->sqlite);
        }
    }

    function getFileLink($file, $test = null, $line = null)
    {
        if ($line) {
            return $this->rooturl . '?file=' . urlencode($file) . '&line=' . $line;
        }
        if ($test) {
            return $this->rooturl . '?file=' . urlencode($file) . '&test=' . $test;
        }
        return $this->rooturl . '?file=' . urlencode($file);
    }

    function getTOCLink($test = false)
    {
        if ($test === true) {
            return $this->rooturl . '?test=TOC';
        }
        if ($test) {
            return $this->rooturl . '?test=' . urlencode($test);
        }
        return $this->rooturl;
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
}
?>
