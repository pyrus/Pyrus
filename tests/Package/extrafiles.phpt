--TEST--
\Pyrus\Package\Creator: extrafiles may contain strings (#71)
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
$pf = new \Pyrus\PackageFile\v2;

$pf->name = 'testing2';
$pf->channel = 'pear2.php.net';
$pf->summary = 'testing';
$pf->description = 'hi description';
$pf->notes = 'my notes';
$pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');
$pf->setPackagefile(TESTDIR . '/package.xml');

$package = new \Pyrus\Package(false);
$xmlcontainer = new \Pyrus\PackageFile($pf);
$xml = new \Pyrus\Package\Xml(TESTDIR . '/package.xml', $package, $xmlcontainer);
$package->setInternalPackage($xml);

$vendorDir = __DIR__ . '/../../vendor/php/PEAR2/';
$outfile = $package->name.'-'.$package->version['release'];
$a = new \Pyrus\Package\Creator(
    array(
        new \Pyrus\Developer\Creator\Phar(
            $outfile.'.tgz',
            false,
            Phar::TAR, Phar::GZ
        )
    ),
    $vendorDir,
    $vendorDir,
    $vendorDir
);

// Try to include the current file as part of the test suite.
$a->render($package, array('tests/extrafiles.phpt' => __FILE__));

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===
