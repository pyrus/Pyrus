<?php
$package->dependencies['required']->php = '5.3.1RC1';
$package->dependencies['required']->package['pear2.php.net/PEAR2_Autoload']->min('0.2.0');
$package->dependencies['required']->package['pear2.php.net/PEAR2_Exception']->min('0.2.0');
$package->dependencies['required']->package['pear2.php.net/PEAR2_MultiErrors']->min('0.2.0');
$package->dependencies['required']->package['pear2.php.net/PEAR2_HTTP_Request']->min('0.2.0');
$package->dependencies['required']->package['pear2.php.net/PEAR2_Console_CommandLine']->min('0.1.0');

$compatible->dependencies['required']->php = '5.3.1RC1';
$compatible->dependencies['required']->package['pear2.php.net/PEAR2_Autoload']->min('0.2.0');
$compatible->dependencies['required']->package['pear2.php.net/PEAR2_Exception']->min('0.2.0');
$compatible->dependencies['required']->package['pear2.php.net/PEAR2_MultiErrors']->min('0.2.0');
$compatible->dependencies['required']->package['pear2.php.net/PEAR2_HTTP_Request']->min('0.2.0');
$compatible->dependencies['required']->package['pear2.php.net/PEAR2_Console_CommandLine']->min('0.1.0');

$package->files['scripts/pyrus'] = array(
    'attribs' => array('role' => 'script'),
    'tasks:replace' => array('attribs' =>
                             array('from' => '@php_dir@', 'to' => 'php_dir', 'type' => 'pear-config'))
);

