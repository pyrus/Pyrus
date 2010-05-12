<?php
include dirname(__DIR__) . '/setup.php.inc';
@mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
