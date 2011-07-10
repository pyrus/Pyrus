--TEST--
\Pyrus\ScriptFrontend\Commands::upgrade(), basic test
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$c = getTestConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.prepare.explicitstate',
                       'http://pear2.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';

\Pyrus\Installer::begin();
\Pyrus\Installer::prepare(new \Pyrus\Package(__DIR__ .
                                '/../../Mocks/Internet/install.prepare.explicitstate/get/P2-1.0.0.tar'));
\Pyrus\Installer::commit();

ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands(true);
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