--TEST--
\Pyrus\ScriptFrontend\Commands::upgrade(), upgrade of dependency
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$c = getTestConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.prepare.explicitstate',
                       'http://pear2.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';
\Pyrus\Config::current()->preferred_state = 'beta';

\Pyrus\Installer::begin();
\Pyrus\Installer::prepare(new \Pyrus\Package(__DIR__ .
                                '/../../Mocks/Internet/install.prepare.explicitstate/get/P2-1.0.0.tar'));
\Pyrus\Installer::commit();

$test->assertEquals(true, isset(\Pyrus\Config::current()->registry->package['pear2.php.net/P2']),
    'ensure setup install of P2 worked');

ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'upgrade', __DIR__ .
                                '/../../Mocks/Internet/install.prepare.explicitstate/get/P1-1.1.0RC1.tar'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . 'Downloading pear2.php.net/P1
Downloading pear2.php.net/P2

Installed pear2.php.net/P1-1.1.0RC1
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