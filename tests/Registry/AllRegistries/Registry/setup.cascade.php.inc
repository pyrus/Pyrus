<?php
require __DIR__ . '/../../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit2');
$reg2 = new \pear2\Pyrus\Registry(__DIR__.'/testit2');
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'testit' . PATH_SEPARATOR .
                 __DIR__ . DIRECTORY_SEPARATOR . 'testit2');
$c = \pear2\Pyrus\Config::singleton(__DIR__.'/testit', __DIR__. '/testit/plugins/notused.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
require __DIR__ . '/../../../PackageFile_v2/setupFiles/setupPackageFile.php.inc';
$reg = new \pear2\Pyrus\Registry(__DIR__.'/testit');
$reg->setParent($reg2);
