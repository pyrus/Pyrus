<?php
function __autoload($class)
{
    if (substr($class, 0, 4) != 'PEAR') return false;
    $path = explode('_', substr($class, 11)); // strip PEAR2_Pyrus for CVS
    $path = dirname(__FILE__) . implode('\\', $path) . '.php';
    include $path;
}
include $a = 'C:/development/PEAR2/Exception/trunk/src/Exception.php';
include $b = 'C:/development/PEAR2/MultiErrors/trunk/src/MultiErrors.php';
include 'C:/development/PEAR2/MultiErrors/trunk/src/MultiErrors/Exception.php';
//include 'C:/development/PEAR2/Pyrus_Developer/src/Developer/PackageFile/PEAR2SVN.php';
//include 'C:/development/PEAR2/Pyrus_Developer/src/Developer/PackageFile/PEAR2SVN/Filter.php';
//include 'C:/development/PEAR2/Pyrus_Developer/src/Developer/PackageFile/v2.php';
//new PEAR2_Pyrus_Developer_PackageFile_PEAR2SVN(
//    'C:/development/PEAR2/Autoload', 'PEAR2_Autoload', 'pear2.php.net',
//        false, false);
////    'C:/development/PEAR2/Pyrus', 'PEAR2_Pyrus', 'pear2.php.net');
//new PEAR2_Pyrus_Package('C:/development/PEAR2/Pyrus_Developer/package.xml');
//exit;
//include 'C:/development/PEAR2/HTTP/Request/src/HTTP/Request/allfiles.php';
//include 'C:/development/PEAR2/Pyrus_Developer/Creator/Zip.php';
//include 'C:/development/PEAR2/Pyrus_Developer/Creator/Tar.php';
//include 'C:/development/PEAR2/Pyrus_Developer/Creator/Xml.php';
//include 'C:/development/PEAR2/Pyrus_Developer/Creator/Exception.php';
//$a = new PEAR2_Pyrus_Package_Creator(array(
//        new PEAR2_Pyrus_Developer_Creator_Zip('C:/development/PEAR2/blah.zip'),
//        new PEAR2_Pyrus_Developer_Creator_Tar('C:/development/PEAR2/blah.tgz'),
//        new PEAR2_Pyrus_Developer_Creator_Xml('C:/development/PEAR2/blah.xml'),
//    ), $a, 'C:/development/PEAR2/Autoload/Autoload.php', $b);
//$b = new PEAR2_Pyrus_Package('C:/development/pear-core/PEAR-1.6.0.tgz');
//$a->render($b);
//exit;
//$pf = new PEAR2_Pyrus_PackageFile_v2;
//$pf->name = 'test';
//$pf->channel = 'pear.php.net';
//$pf->summary = 'test';
//$pf->description = 'testing';
//$pf->maintainer['cellog']->name('Greg Beaver')->role('lead')->email('cellog@php.net')
//    ->active('yes');
//$a = new DateTime();
//$pf->date = $a->format('Y-m-d');
//$pf->version['release'] = '1.0.0';
//$pf->version['api'] = '1.0.0';
//$pf->stability['release'] = 'stable';
//$pf->stability['api'] = 'stable';
//$pf->license = 'PHP License';
//$pf->notes = 'test';
//$pf->dependencies->required->php = array('min' => '5.2.0');
//$pf->dependencies->required->pearinstaller = array('min' => '5.2.0', 'exclude' => '1.2.3');
//$pf->files['test/me.php'] = array('attribs' => array('role' => 'php'));
//$pf = new PEAR2_Pyrus_package(dirname(__FILE__) . '/test.xml');
//foreach ($pf->packagingcontents as $name => $file) {
//    var_dump($name, $file);
//}
//exit;
define('OS_WINDOWS', true);
define('OS_UNIX', false);
$g = PEAR2_Pyrus_Config::singleton('C:/development/pear-core/testpear');
//$g = new PEAR2_Pyrus_Config('/home/cellog/testpear');
try {
    PEAR2_Pyrus_Installer::$options['force'] = true;
    PEAR2_Pyrus_Installer::begin();
    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('C:/development/PEAR2/Autoload/trunk/package.xml'));
    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('C:/development/PEAR2/Exception/trunk/package.xml'));
    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('C:/development/PEAR2/MultiErrors/trunk/package.xml'));
    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('C:/development/PEAR2/Pyrus_Developer/package.xml'));
    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('C:/development/PEAR2/Pyrus/trunk/pyrus.phar'));
//    PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('C:/development/PEAR2/Pyrus/trunk/package.xml'));
    PEAR2_Pyrus_Installer::commit();
} catch (Exception $e) {
    PEAR2_Pyrus_Installer::rollback();
    echo $e;
}
exit;
