<?php
namespace {
$force = false;
$norender = false;
if (isset($_SERVER['argv'][1])) {
    $arg = $_SERVER['argv'][1];
    if ($arg === '--force') {
        $force = true;
        if (!isset($_SERVER['argv'][2])) {
            goto skippy;
        }
        // check if we only want to rebuild the coverage db
        if ($_SERVER['argv'][2] === '--norender') {
            $norender = true;
            if (!isset($_SERVER['argv'][3])) {
                goto skippy;
            }
        }
    } elseif ($arg === '--norender') {
        $norender = true;
        if (!isset($_SERVER['argv'][2])) {
            goto skippy;
        }
        if ($_SERVER['argv'][2] === '--force') {
            $force = true;
            if (!isset($_SERVER['argv'][3])) {
                goto skippy;
            }
        }
    }
}
skippy:
function __autoload($c)
{
    $c = str_replace(array('Pyrus\Developer\CoverageAnalyzer\\',
                           '\\'), array('', '/'), $c);
    include __DIR__ . '/' . $c . '.php';
}
$e = error_reporting();
error_reporting(0);
require_once 'PEAR/Command/Test.php';
require_once 'PEAR/Frontend/CLI.php';
require_once 'PEAR/Config.php';
$cli = new PEAR_Frontend_CLI;
$config = @PEAR_Config::singleton();
$test = new PEAR_Command_Test($cli, $config);
error_reporting($e);
}
namespace Pyrus\Developer\CoverageAnalyzer {
    $codepath = realpath('../../../../../Pyrus/src');
    $testpath = realpath('../../../../../Pyrus/tests');
    $sqlite = new Sqlite($testpath . '/pear2coverage.db', $codepath, $testpath);
    $modified = $sqlite->getModifiedTests();
    if (!count($modified)) {
        if ($force) {
            goto dorender;
        }
        echo "No changes to coverage needed.  Bye!\n";
        exit;
    }
    $dir = getcwd();
    chdir($testpath);
    error_reporting(0);
    $test->doRunTests('run-tests', array('coverage' => true), $modified);
    error_reporting($e);
    chdir($dir);
    if (file_exists($testpath . '/run-tests.log')) {
        // tests failed
        echo "Tests failed - not regenerating coverage data\n";
        exit;
    }
dorender:
    $a = new Aggregator($testpath,
                        $codepath,
                        $testpath . '/pear2coverage.db');
    if ($norender) {
        exit;
    }
    if (file_exists(__DIR__ . '/test')) {
        foreach (new \DirectoryIterator(__DIR__ . '/test') as $file) {
            if ($file->isDot()) continue;
            unlink($file->getPathName());
        }
    } else {
        mkdir(__DIR__ . '/test');
    }
    echo "Rendering\n";
    $a->render(__DIR__ . '/test');
    echo "Done rendering\n";
}
?>
