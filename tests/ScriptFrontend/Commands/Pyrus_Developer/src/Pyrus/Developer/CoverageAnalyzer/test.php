<?php
namespace {
function __autoload($c)
{
    $c = str_replace(array('Pyrus\Developer\CoverageAnalyzer\\',
                           '\\'), array('', '/'), $c);
    include __DIR__ . '/' . $c . '.php';
}
}
namespace Pyrus\Developer\CoverageAnalyzer {
    $a = new Aggregator($testpath = realpath(__DIR__ . '/../../../../../Pyrus/tests'),
                        realpath(__DIR__ . '/../../../../../Pyrus/src'),
                        $testpath . '/pear2coverage.db');
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
