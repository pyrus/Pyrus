--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::install() --optionaldeps not specified
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$c = getTestConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.optionaldeps',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';

ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'install', 'pear2/P1', 'P6'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n" .
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
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===