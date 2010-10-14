--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::install() --optionaldeps
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
$cli->run($args = array (TESTDIR, 'install', '--optionaldeps', 'pear2/P1'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n" .
'

Downloading pear2.php.net/P1

Downloading pear2.php.net/P2
Downloading pear2.php.net/P3
Downloading pear2.php.net/P4
Installed pear2.php.net/P1-1.0.0
Installed pear2.php.net/P2-1.0.0
Installed pear2.php.net/P3-1.0.0
Installed pear2.php.net/P4-1.0.0
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