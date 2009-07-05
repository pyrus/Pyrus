<?php
include __DIR__ . '/../test_framework.php.inc';
@mkdir(MYDIR . DIRECTORY_SEPARATOR . 'testit');
set_include_path(MYDIR . DIRECTORY_SEPARATOR . 'testit');
$c = \pear2\Pyrus\Config::singleton(MYDIR.'/testit', MYDIR . '/testit/plugins/pearconfig.xml');
restore_include_path();
$c->saveConfig();

