<?php
include __DIR__ . '/../test_framework.php.inc';
require __DIR__ . '/validator.php.inc';
$errs = new \PEAR2\MultiErrors;
if (!isset($state)) {
    $state = 'INSTALLING';
}
if (!isset($package)) {
    $package = array('channel' => 'pear2.php.net', 'package' => 'test');
}
switch ($state) {
    case 'INSTALLING' :
        $state = \Pyrus\Validate::INSTALLING;
        break;
    case 'DOWNLOADING' :
        $state = \Pyrus\Validate::DOWNLOADING;
        break;
    case 'UNINSTALLING' :
        $state = \Pyrus\Validate::UNINSTALLING;
        break;
    case 'NORMAL' :
        $state = \Pyrus\Validate::NORMAL;
        break;
    case 'PACKAGING' :
        $state = \Pyrus\Validate::PACKAGING;
        break;
}
$validator = new test_Validator($package, $state, $errs);