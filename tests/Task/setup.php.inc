<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
@mkdir(MYDIR . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
$c->bin_dir = MYDIR . '/testit/bin';
restore_include_path();
$c->saveConfig();
