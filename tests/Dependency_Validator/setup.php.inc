<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
require __DIR__ . '/validator.php.inc';
$errs = new PEAR2_MultiErrors;
if (!isset($state)) {
    $state = 'INSTALLING';
}
if (!isset($package)) {
    $package = 'pear2.php.net/test';
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