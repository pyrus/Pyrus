--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::listChannels()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands(true);
$cli->run($args = array (0 => 'mypear',
                         1 => __DIR__ . '/testit'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . "\n" .
                    'Setting my pear repositories to:' . "\n" . __DIR__ . DIRECTORY_SEPARATOR . 'testit',
                    $contents,
                    'set my pear path');
ob_start();

$cli->run($args = array (0 => 'list-channels'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Listing channels [' . __DIR__ . DIRECTORY_SEPARATOR . 'testit' . ']:' . "\n"
                    . '__uri (__uri)' . "\n"
                    . 'doc.php.net (phpdocs)' . "\n"
                    . 'pear.php.net (pear)' . "\n"
                    . 'pear2.php.net (pear2)' . "\n"
                    . 'pecl.php.net (pecl)' . "\n",
                    $contents,
                    'list channels');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===