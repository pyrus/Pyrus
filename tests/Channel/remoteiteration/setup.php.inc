<?php
include dirname(__DIR__) . '/setup.php.inc';
@mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(MYDIR . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit', __DIR__ . '/pearconfig.xml');
$c->bin_dir = MYDIR . '/testit/bin';
restore_include_path();
$c->saveConfig();
