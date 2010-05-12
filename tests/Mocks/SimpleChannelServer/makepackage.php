<?php
error_reporting(E_ALL);
ini_set('display_errors',true);
require_once dirname(__FILE__).'/../../autoload.php';

$a = new \PEAR2\Pyrus\Developer\PackageFile\PEAR2SVN(dirname(__FILE__), 'PEAR2_SimpleChannelServer');

$package = new \PEAR2\Pyrus\Package('package.xml');
$outfile = $package->name.'-'.$package->version['release'];
$a = new \PEAR2\Pyrus\Package\Creator(array(
                    //new \PEAR2\Pyrus\Developer\Creator\Tar($outfile.'.tar', 'none'),
                    new \PEAR2\Pyrus\Developer\Creator\Phar($outfile.'.tgz', false, Phar::TAR, Phar::GZ),),
                    dirname(__FILE__).'/../../Exception/src',
					dirname(__FILE__).'/../../Autoload/src',
					dirname(__FILE__).'/../../MultiErrors/src');
$a->render($package);

$a = new \PEAR2\Pyrus\Package\Creator(array(
                    new \PEAR2\Pyrus\Developer\Creator\Phar\PHPArchive(__DIR__ . '/pearscs.phar', '<?php
function __autoload($class)
{
    include \'phar://\' . PYRUS_PHAR_FILE . \'/php/\' . implode(\'/\', explode(\'_\', $class)) . \'.php\';
}
set_include_path(\'phar://\' . PYRUS_PHAR_FILE . \'/php/\'.PATH_SEPARATOR.get_include_path());
$cli = new pear\SimpleChannelServer\CLI();
$cli->process();
'),),
                    dirname(__FILE__) . '/../../Exception/src',
                    dirname(__FILE__) . '/../../Autoload/src',
                    dirname(__FILE__) . '/../../MultiErrors/src');
$b = new \PEAR2\Pyrus\Package(__DIR__ . '/package.xml');
$rp = __DIR__ . '/../../HTTP_Request/src/HTTP';

$additional_files = array(
    'php/PEAR2/HTTP/Request.php'                   => $rp . '/Request.php',
    'php/PEAR2/HTTP/Request/Adapter.php'           => $rp . '/Request/Adapter.php',
    'php/PEAR2/HTTP/Request/Adapter/Phpsocket.php' => $rp . '/Request/Adapter/Phpsocket.php',
    'php/PEAR2/HTTP/Request/Adapter/Phpstream.php' => $rp . '/Request/Adapter/Phpstream.php',
    'php/PEAR2/HTTP/Request/Exception.php'         => $rp . '/Request/Exception.php',
    'php/PEAR2/HTTP/Request/Headers.php'           => $rp . '/Request/Headers.php',
    'php/PEAR2/HTTP/Request/Response.php'          => $rp . '/Request/Response.php',
    'php/PEAR2/HTTP/Request/Uri.php'               => $rp . '/Request/Uri.php',
);
$pyrus = new \PEAR2\Pyrus\Package(__DIR__ . '/../../Pyrus/package.xml');
$pyrus_developer = new \PEAR2\Pyrus\Package(__DIR__ . '/../../Pyrus_Developer/package.xml');
$exception = new \PEAR2\Pyrus\Package(__DIR__ . '/../../Exception/package.xml');
foreach (array('Pyrus'           => $pyrus,
               'Pyrus_Developer' => $pyrus_developer,
               'Exception'       => $exception) as $add_dir=>$add_package) {
    foreach ($add_package->installcontents as $filename=>$details) {
        $add_filename = __DIR__ . '/../../'.$add_dir.'/'.$filename;
        switch($details['attribs']['role']) {
            case 'php':
                $additional_files[str_replace('src/','php/PEAR2/', $filename)] = $add_filename;
                break;
            case 'data':
                $additional_files['php/'.$filename] = $add_filename;
                $additional_files[$filename]        = $add_filename;
                break;
        }
    }
}
$a->render($b, $additional_files);

?>
