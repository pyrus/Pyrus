--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::upgrade(), basic test
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$c = getTestConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.prepare.explicitstate',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';

\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package(__DIR__ .
                                '/../../Mocks/Internet/install.prepare.explicitstate/get/P2-1.0.0.tar'));
\PEAR2\Pyrus\Installer::commit();

ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'upgrade', 'P2-beta'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . 'Downloading pear2.php.net/P2

Installed pear2.php.net/P2-1.1.0RC3' . "\n",
                    $contents,
                    'list packages');

$test->assertFileExists(TESTDIR . '/php/glooby2', 'glooby2');
$test->assertEquals('hi',
                    file_get_contents(TESTDIR . '/php/glooby2'), 'files match');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===