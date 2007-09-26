<?php
// this shows how it works
function __autoload($class)
{
    if (substr($class, 0, 5) != 'PEAR2') return false;
    $path = explode('_', substr($class, 11)); // strip PEAR2_Pyrus for CVS
    $path = dirname(__FILE__) . implode('/', $path) . '.php';
    include $path;
}
include $a = '/home/cellog/workspace/PEAR2/Exception/trunk/src/Exception.php';
include $b = '/home/cellog/workspace/PEAR2/MultiErrors/trunk/src/MultiErrors.php';
include '/home/cellog/workspace/PEAR2/MultiErrors/trunk/src/MultiErrors/Exception.php';
include '/home/cellog/workspace/PEAR2/Pyrus_Developer/Creator/Zip.php';
include '/home/cellog/workspace/PEAR2/Pyrus_Developer/Creator/Tar.php';
include '/home/cellog/workspace/PEAR2/Pyrus_Developer/Creator/Xml.php';
include '/home/cellog/workspace/PEAR2/Pyrus_Developer/Creator/Exception.php';
//$a = new PEAR2_Pyrus_Package_Creator(array(
//        new PEAR2_Pyrus_Developer_Creator_Zip('/tmp/blah.zip'),
//        new PEAR2_Pyrus_Developer_Creator_Tar('/tmp/blah.tgz'),
//        new PEAR2_Pyrus_Developer_Creator_Xml('/tmp/blah.xml'),
//    ), $a, '/home/cellog/workspace/PEAR2/Autoload/trunk/src/Autoload.php', $b);
//$b = new PEAR2_Pyrus_Package('/home/cellog/workspace/pear-core/PEAR-1.6.2.tgz');
//$a->render($b);
//exit;
define('OS_WINDOWS', false);
define('OS_UNIX', true);
//$g = new PEAR2_Pyrus_Config('C:/development/pear-core/testpear');
$g = new PEAR2_Pyrus_Config('/home/cellog/testpear');
$g->saveConfig();
$b = new PEAR2_Pyrus_Package('/home/cellog/workspace/pear-core/PEAR-1.6.2.tgz');
echo $b;
exit;
//$a = new PEAR2_Pyrus_Package('C:/development/pear-core/PEAR-1.5.0a1.tgz');
$a = new PEAR2_Pyrus_Package('/tmp/blah.tgz');
$b = new PEAR2_Pyrus_Installer;
$b->install($a);