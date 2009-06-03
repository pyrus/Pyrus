<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
@mkdir(MYDIR . DIRECTORY_SEPARATOR . 'testit');
set_include_path(MYDIR . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(MYDIR.'/testit', MYDIR . '/testit/plugins/pearconfig.xml');
$c->bin_dir = MYDIR . '/testit/bin';
restore_include_path();
$c->saveConfig();