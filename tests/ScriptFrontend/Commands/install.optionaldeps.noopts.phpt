--TEST--
\pear2\Pyrus\ScriptFrontend\Commands::install() --optionaldeps not specified
--FILE--
<?php
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
require __DIR__ . '/setup.php.inc';
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = \pear2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.optionaldeps',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';

ob_start();
$cli = new \pear2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (__DIR__ . '/testit', 'install', 'pear2/P1', 'P6'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n" .
'
Downloading pear2.php.net/P1

Downloading pear2.php.net/P2
Downloading pear2.php.net/P6
Installed pear2.php.net/P1-1.0.0
Installed pear2.php.net/P2-1.0.0
Installed pear2.php.net/P6-1.0.0
Optional dependencies that will not be installed, use --optionaldeps:
pear2.php.net/P3 depended on by pear2.php.net/P1, pear2.php.net/P6
pear2.php.net/P4 depended on by pear2.php.net/P1
',                     $contents,
                    'list packages');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===