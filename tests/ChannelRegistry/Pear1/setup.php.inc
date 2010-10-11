<?php
include __DIR__ . '/../../test_framework.php.inc';
@mkdir(TESTDIR);
set_include_path(TESTDIR);
$c = \PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '/plugins/pearconfig.xml');
$c->bin_dir = TESTDIR . '/bin';
restore_include_path();
$c->saveConfig();
$creg = new \PEAR2\Pyrus\ChannelRegistry\Pear1(TESTDIR);

