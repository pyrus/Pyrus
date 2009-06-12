<?php
/**
 * This file generates the pyrus.phar file and PEAR2 package for Pyrus.
 *
 * **THIS SCRIPT IS OBSOLETE, USE THE STEPS BELOW INSTEAD**
 *
 * This whole script can be replaced with 2 steps:
 * php pyrus.phar install -pf ../Pyrus_Developer/package.xml
 * php -dphar.readonly=0 pyrus.phar make --package=phar
 */
echo "Tip: Don't use this any more.  Instead, use PEAR2_Pyrus_Developer like so:
php pyrus.phar install -pf ../Pyrus_Developer/package.xml
php -dphar.readonly=0 pyrus.phar make --package=phar
mv PEAR2_Pyrus-2.0.0a1.phar pyrus.phar\n";
error_reporting(E_ALL);
ini_set('display_errors',true);
require_once dirname(__FILE__).'/../autoload.php';

if (ini_get('phar.readonly') != "0") {
    throw new PEAR2_Pyrus_Exception('Error: phar.readonly is not set to "0", pass -d phar.readonly=0 to PHP');
}

$a = new PEAR2_Pyrus_Developer_PackageFile_PEAR2SVN(dirname(__FILE__), 'PEAR2_Pyrus');

$package = new PEAR2_Pyrus_Package(__DIR__ . '/package.xml');

$outfile = $package->name.'-'.$package->version['release'];
$a = new PEAR2_Pyrus_Package_Creator(array(
                    new PEAR2_Pyrus_Developer_Creator_Phar_PHPArchive(__DIR__ . '/pyrus.phar', '<?php
function pyrus_autoload($class)
{
    if (file_exists(\'phar://\' . PYRUS_PHAR_FILE . \'/php/\' . implode(\'/\', explode(\'_\', $class)) . \'.php\')) {
        include \'phar://\' . PYRUS_PHAR_FILE . \'/php/\' . implode(\'/\', explode(\'_\', $class)) . \'.php\';
    }
}
spl_autoload_register("pyrus_autoload");
$frontend = new PEAR2_Pyrus_ScriptFrontend_Commands;
@array_shift($_SERVER[\'argv\']);
$frontend->run($_SERVER[\'argv\']);
'),),
                    dirname(__FILE__) . '/../Exception/src',
					dirname(__FILE__) . '/../Autoload/src',
					dirname(__FILE__) . '/../MultiErrors/src');
$b = new PEAR2_Pyrus_Package(__DIR__ . '/package.xml');
$rp = __DIR__ . '/../HTTP_Request/src/HTTP';
$cc = __DIR__ . '/../sandbox/Console_CommandLine/src/Console';
$a->render($b, array(
    'php/PEAR2/HTTP/Request.php' => $rp . '/Request.php',
    'php/PEAR2/HTTP/Request/Adapter.php' => $rp . '/Request/Adapter.php',
    'php/PEAR2/HTTP/Request/Adapter/Curl.php' => $rp . '/Request/Adapter/Curl.php',
    'php/PEAR2/HTTP/Request/Adapter/Http.php' => $rp . '/Request/Adapter/Http.php',
    'php/PEAR2/HTTP/Request/Adapter/Phpsocket.php' => $rp . '/Request/Adapter/Phpsocket.php',
    'php/PEAR2/HTTP/Request/Adapter/Phpstream.php' => $rp . '/Request/Adapter/Phpstream.php',
    'php/PEAR2/HTTP/Request/Exception.php' => $rp . '/Request/Exception.php',
    'php/PEAR2/HTTP/Request/Headers.php' => $rp . '/Request/Headers.php',
    'php/PEAR2/HTTP/Request/Listener.php' => $rp . '/Request/Listener.php',
    'php/PEAR2/HTTP/Request/Response.php' => $rp . '/Request/Response.php',
    'php/PEAR2/HTTP/Request/Uri.php' => $rp . '/Request/Uri.php',

    'php/PEAR2/Console/CommandLine.php' => $cc . '/CommandLine.php',
    'php/PEAR2/Console/CommandLine/Result.php' => $cc . '/CommandLine/Result.php',
    'php/PEAR2/Console/CommandLine/Renderer.php' => $cc . '/CommandLine/Renderer.php',
    'php/PEAR2/Console/CommandLine/Outputter.php' => $cc . '/CommandLine/Outputter.php',
    'php/PEAR2/Console/CommandLine/Option.php' => $cc . '/CommandLine/Option.php',
    'php/PEAR2/Console/CommandLine/MessageProvider.php' => $cc . '/CommandLine/MessageProvider.php',
    'php/PEAR2/Console/CommandLine/Exception.php' => $cc . '/CommandLine/Exception.php',
    'php/PEAR2/Console/CommandLine/Element.php' => $cc . '/CommandLine/Element.php',
    'php/PEAR2/Console/CommandLine/Command.php' => $cc . '/CommandLine/Command.php',
    'php/PEAR2/Console/CommandLine/Argument.php' => $cc . '/CommandLine/Argument.php',
    'php/PEAR2/Console/CommandLine/Action.php' => $cc . '/CommandLine/Action.php',
    'php/PEAR2/Console/CommandLine/Renderer/Default.php' => $cc . '/CommandLine/Renderer/Default.php',
    'php/PEAR2/Console/CommandLine/Outputter/Default.php' => $cc . '/CommandLine/Outputter/Default.php',
    'php/PEAR2/Console/CommandLine/MessageProvider/Default.php' => $cc . '/CommandLine/MessageProvider/Default.php',
    'php/PEAR2/Console/CommandLine/Action/Callback.php' => $cc . '/CommandLine/Action/Callback.php',
    'php/PEAR2/Console/CommandLine/Action/Counter.php' => $cc . '/CommandLine/Action/Counter.php',
    'php/PEAR2/Console/CommandLine/Action/Help.php' => $cc . '/CommandLine/Action/Help.php',
    'php/PEAR2/Console/CommandLine/Action/StoreFloat.php' => $cc . '/CommandLine/Action/StoreFloat.php',
    'php/PEAR2/Console/CommandLine/Action/StoreInt.php' => $cc . '/CommandLine/Action/StoreInt.php',
    'php/PEAR2/Console/CommandLine/Action/StoreString.php' => $cc . '/CommandLine/Action/StoreString.php',
    'php/PEAR2/Console/CommandLine/Action/StoreTrue.php' => $cc . '/CommandLine/Action/StoreTrue.php',
    'php/PEAR2/Console/CommandLine/Action/Version.php' => $cc . '/CommandLine/Action/Version.php',
));
