<?php
include __DIR__ . '/../../test_framework.php.inc';
@mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
restore_include_path();
$c->saveConfig();
$creg = new PEAR2_Pyrus_ChannelRegistry_Sqlite3(__DIR__ . '/testit');

