<?php
include __DIR__ . '/../test_framework.php.inc';
@mkdir(MYDIR . DIRECTORY_SEPARATOR . 'testit');
set_include_path(MYDIR . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(MYDIR.'/testit');
restore_include_path();
$c->saveConfig();

