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
echo "Tip: Don't use this any more.  Instead, use \pear2\Pyrus\Developer like so:
php pyrus.phar install -pf ../Pyrus_Developer/package.xml
php -dphar.readonly=0 pyrus.phar make --package=phar
mv PEAR2_Pyrus-2.0.0a1.phar pyrus.phar\n";
error_reporting(E_ALL);
ini_set('display_errors',true);
require_once dirname(__FILE__).'/../autoload.php';

if (ini_get('phar.readonly') != "0") {
    throw new \pear2\Pyrus\Exception('Error: phar.readonly is not set to "0", pass -d phar.readonly=0 to PHP');
}

//$a = new \pear2\Pyrus\Developer\PackageFile\PEAR2SVN(dirname(__FILE__), 'PEAR2_Pyrus');

$package = new \pear2\Pyrus\Package(__DIR__ . '/package.xml');

$creator = new pear2\Pyrus\Package\Creator(array(
            new pear2\Pyrus\Developer\Creator\Phar(
                'PEAR2_Pyrus-2.0.0a1.phar',
                file_get_contents(__DIR__ . '/stub.php'),
                Phar::PHAR, PHAR::NONE,
                array(), null, $package, null, null)),
                                        dirname(__FILE__) . '/../Exception/src',
					dirname(__FILE__) . '/../Autoload/src',
					dirname(__FILE__) . '/../MultiErrors/src');
$extrafiles = '';
include __DIR__ . '/extrasetup.php';
$creator->render($package, $extrafiles);
