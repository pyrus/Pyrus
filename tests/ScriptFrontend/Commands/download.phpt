--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::download()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$c = getTestConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../../Mocks/Internet/install.prepare.explicitstate',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';

mkdir(TESTDIR . '/oksavehere');
chdir(TESTDIR . '/oksavehere');
ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'download', __DIR__ .
                                '/../../Mocks/Internet/install.prepare.explicitstate/get/P1-1.1.0RC1.tar',
                                'P2-beta', 'P1', 'unknown', 'P3-1.1.0RC2'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . "failed to init unknown for download (package unknown does not exist)\n"
                    . 'Downloading ' . __DIR__ .
                                '/../../Mocks/Internet/install.prepare.explicitstate/get/P1-1.1.0RC1.tar...' .
                                "done (" . TESTDIR .
                                DIRECTORY_SEPARATOR . 'oksavehere' . DIRECTORY_SEPARATOR . "P1-1.1.0RC1.tar)\n"
                    . 'Downloading P2-beta...' .
                                "done (" . TESTDIR .
                                DIRECTORY_SEPARATOR . 'oksavehere' . DIRECTORY_SEPARATOR . "P2-1.1.0RC3.tgz)\n"
                    . 'Downloading P1...' .
                                "done (" . TESTDIR .
                                DIRECTORY_SEPARATOR . 'oksavehere' . DIRECTORY_SEPARATOR . "P1-1.0.0.tgz)\n"
                    . 'Downloading P3-1.1.0RC2...' .
                                "failed! (Could not download abstract package pear2.php.net/P3)\n",
                     $contents,
                    'list packages');

$test->assertFileExists(TESTDIR . '/oksavehere/P1-1.1.0RC1.tar', 'P1 tarball');
$test->assertFileExists(TESTDIR . '/oksavehere/P2-1.1.0RC3.tgz', 'P2-beta');
$test->assertFileExists(TESTDIR . '/oksavehere/P1-1.0.0.tgz', 'P1');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===