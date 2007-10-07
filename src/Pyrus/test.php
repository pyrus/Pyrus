<?php
include '/home/cellog/testpear/php/PEAR2/Autoload.php';
$a = new PEAR2_Pyrus_Package_Creator(array(
        new PEAR2_Pyrus_Developer_Creator_Phar_PHPArchive('/home/cellog/workspace/Pyrus/pyrus.phar'),
    ));
$b = new PEAR2_Pyrus_Package('/home/cellog/workspace/Pyrus/package.xml');
$a->render($b);
exit;
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
include '/home/cellog/workspace/PEAR2/Pyrus_Developer/src/Developer/PackageFile/v2.php';
include '/home/cellog/workspace/PEAR2/Pyrus_Developer/src/Developer/PackageFile/PEAR2SVN.php';
include '/home/cellog/workspace/PEAR2/Pyrus_Developer/src/Developer/PackageFile/PEAR2SVN/Filter.php';
//new PEAR2_Pyrus_Developer_PackageFile_PEAR2SVN(
//    '/home/cellog/workspace/PEAR2/Pyrus_Developer', 'PEAR2_Pyrus_Developer', 'pear2.php.net', false, false);
//exit;
//include '/home/cellog/workspace/PEAR2/Pyrus_Developer/Creator/Zip.php';
//include '/home/cellog/workspace/PEAR2/Pyrus_Developer/src/Developer/Creator/Phar.php';
//include '/home/cellog/workspace/PEAR2/Pyrus_Developer/Creator/Xml.php';
//include '/home/cellog/workspace/PEAR2/Pyrus_Developer/src/Developer/Creator/Exception.php';
//$a = new PEAR2_Pyrus_Package_Creator(array(
//        new PEAR2_Pyrus_Developer_Creator_Phar('/home/cellog/workspace/Pyrus/blah.phar',
//            '<?php echo "hi";__HALT_COMPILER();'),
//        new PEAR2_Pyrus_Developer_Creator_Tar('/tmp/blah.tgz'),
//        new PEAR2_Pyrus_Developer_Creator_Xml('/tmp/blah.xml'),
//    ), $a, '/home/cellog/workspace/PEAR2/Autoload/trunk/src/Autoload.php', $b);
//$b = new PEAR2_Pyrus_Package('/home/cellog/workspace/pear-core/PEAR-1.6.2.tgz');
//$a->render($b);
//exit;
define('OS_WINDOWS', false);
define('OS_UNIX', true);
include '/home/cellog/workspace/PEAR2/HTTP_Request/trunk/src/HTTP/Request/allfiles.php';
$g = new PEAR2_Pyrus_Config('/home/cellog/testpear');
try {
    PEAR2_Pyrus_Installer::begin();
    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('/home/cellog/workspace/PEAR2/Autoload/trunk/package.xml'));
    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('/home/cellog/workspace/PEAR2/Exception/trunk/package.xml'));
    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('/home/cellog/workspace/PEAR2/MultiErrors/trunk/package.xml'));
    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('/home/cellog/workspace/PEAR2/Pyrus_Developer/package.xml'));
    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('/home/cellog/workspace/Pyrus/package.xml'));
    PEAR2_Pyrus_Installer::commit();
} catch (Exception $e) {
    PEAR2_Pyrus_Installer::rollback();
    echo $e;
}
exit;
//$g = new PEAR2_Pyrus_Config('C:/development/pear-core/testpear');
$g = new PEAR2_Pyrus_Config('/home/cellog/testpear');
$g->saveConfig();
$g->bin_dir = 'home/cellog/testpear';
//$a = new PEAR2_Pyrus_Package('C:/development/pear-core/PEAR-1.5.0a1.tgz');
$a = new PEAR2_Pyrus_Package('/tmp/blah.tgz');
$b = new PEAR2_Pyrus_Installer;
$b->install($a);