--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::listChannels()
--FILE--
<?php
set_include_path(dirname(__FILE__).'/testit');
require dirname(dirname(__FILE__)) . '/setup.php.inc';
ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (0 => 'mypear',
                         1 => __DIR__ . '/testit'));

$test->assertEquals('Setting my pear reposities to:' . PHP_EOL . __DIR__ . '/testit',
                    ob_get_contents(),
                    'set my pear path');


$cli->run($args = array (0 => 'list-channels'));

$test->assertEquals('Using PEAR installation found at ' . __DIR__ . '/testit' . PHP_EOL
                    . 'Listing channels [' . __DIR__ . '/testit' . ']:' . PHP_EOL
                    . '__uri (__uri)' . PHP_EOL
                    . 'pear.php.net (pear)' . PHP_EOL
                    . 'pear2.php.net (pear2)' . PHP_EOL
                    . 'pecl.php.net (pecl)' . PHP_EOL,
                    ob_get_contents(),
                    'list channels');
ob_end_clean();
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===