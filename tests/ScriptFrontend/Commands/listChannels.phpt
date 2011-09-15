--TEST--
\Pyrus\ScriptFrontend\Commands::listChannels()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (0 => 'mypear',
                         1 => TESTDIR));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n" .
                    'Setting my pear repositories to:' . "\n" . TESTDIR,
                    $contents,
                    'set my pear path');
ob_start();

$cli->run($args = array (0 => 'list-channels'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . 'Listing channels [' . TESTDIR . ']:' . "\n"
                    . '__uri (__uri)' . "\n"
                    . 'doc.php.net (phpdocs)' . "\n"
                    . 'pear.php.net (pear)' . "\n"
                    . 'pear2.php.net (pear2)' . "\n"
                    . 'pecl.php.net (pecl)' . "\n"
                    . 'pyrus.net (pyrus)' . "\n",
                    $contents,
                    'list channels');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===