<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();
require __DIR__ . '/validator.php.inc';
$errs = new PEAR2_MultiErrors;
if (!isset($state)) {
    $state = 'INSTALLING';
}
if (!isset($package)) {
    $package = array('channel' => 'pear2.php.net', 'package' => 'test');
}
switch ($state) {
    case 'INSTALLING' :
        $state = PEAR2_Pyrus_Validate::INSTALLING;
        break;
    case 'DOWNLOADING' :
        $state = PEAR2_Pyrus_Validate::DOWNLOADING;
        break;
    case 'UNINSTALLING' :
        $state = PEAR2_Pyrus_Validate::UNINSTALLING;
        break;
    case 'NORMAL' :
        $state = PEAR2_Pyrus_Validate::NORMAL;
        break;
    case 'PACKAGING' :
        $state = PEAR2_Pyrus_Validate::PACKAGING;
        break;
}
$validator = new test_Validator($package, $state, $errs);