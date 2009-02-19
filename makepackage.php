<?php
/**
 * This file generates the pyrus.phar file and PEAR2 package for Pyrus.
 */
error_reporting(E_ALL);
ini_set('display_errors',true);
require_once dirname(__FILE__).'/../autoload.php';

if (ini_get('phar.readonly') != "0") {
    throw new PEAR2_Pyrus_Exception('Error: phar.readonly is not set to "0" in your php.ini');
}

$a = new PEAR2_Pyrus_Developer_PackageFile_PEAR2SVN(dirname(__FILE__), 'PEAR2_Pyrus');

$package = new PEAR2_Pyrus_Package(__DIR__ . '/package.xml');

$outfile = $package->name.'-'.$package->version['release'];
$a = new PEAR2_Pyrus_Package_Creator(array(
                    new PEAR2_Pyrus_Developer_Creator_Phar_PHPArchive(__DIR__ . '/pyrus.phar', '<?php
function __autoload($class)
{
    include \'phar://\' . PYRUS_PHAR_FILE . \'/src/\' . implode(\'/\', explode(\'_\', $class)) . \'.php\';
}
$frontend = new PEAR2_Pyrus_ScriptFrontend_Commands;
@array_shift($_SERVER[\'argv\']);
$frontend->run($_SERVER[\'argv\']);
'),),
                    dirname(__FILE__) . '/../Exception/src/Exception.php',
					dirname(__FILE__) . '/../Autoload/src/Autoload.php',
					dirname(__FILE__) . '/../MultiErrors/src');
$b = new PEAR2_Pyrus_Package(__DIR__ . '/package.xml');
$rp = __DIR__ . '/../HTTP_Request/src/HTTP';
$a->render($b, array(
    'src/PEAR2/HTTP/Request.php' => $rp . '/Request.php',
    'src/PEAR2/HTTP/Request/Adapter.php' => $rp . '/Request/Adapter.php',
    'src/PEAR2/HTTP/Request/Adapter/Curl.php' => $rp . '/Request/Adapter/Curl.php',
    'src/PEAR2/HTTP/Request/Adapter/Phpsocket.php' => $rp . '/Request/Adapter/Phpsocket.php',
    'src/PEAR2/HTTP/Request/Adapter/Phpstream.php' => $rp . '/Request/Adapter/Phpstream.php',
    'src/PEAR2/HTTP/Request/Exception.php' => $rp . '/Request/Exception.php',
    'src/PEAR2/HTTP/Request/Headers.php' => $rp . '/Request/Headers.php',
    'src/PEAR2/HTTP/Request/Response.php' => $rp . '/Request/Response.php',
    'src/PEAR2/HTTP/Request/Uri.php' => $rp . '/Request/Uri.php',
));
