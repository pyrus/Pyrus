<?php
require __DIR__ . '/../../../../autoload.php';
class InternetMaker
{
    var $__DIR__;
    var $chan;
    var $scs;
    function __construct($__DIR__)
    {
        set_include_path($__DIR__);
        $c = \pear2\Pyrus\Config::singleton(dirname($__DIR__), dirname($__DIR__) . '/pearconfig.xml');
        $c->bin_dir = $__DIR__ . '/bin';
        restore_include_path();
        $c->saveConfig();

        $chan = new pear2\SimpleChannelServer\Channel('pear2.php.net', 'unit test channel');
        $scs = new pear2\SimpleChannelServer\Main($chan, $__DIR__, dirname($__DIR__) . '/PEAR2');

        $scs->saveChannel();
        $this->chan = $chan;
        $this->scs = $scs;
        $this->__DIR__ = $__DIR__;
    }

    function getPassablePf($name, $version, $state = 'stable')
    {
        $pf = new \pear2\Pyrus\PackageFile\v2;
        $pf->name = $name;
        $pf->channel = 'pear2.php.net';
        $pf->summary = 'testing';
        $pf->version['release'] = $version;
        $pf->stability['release'] = $state;
        $pf->description = 'hi description';
        $pf->notes = 'my notes';
        $pf->maintainer['cellog']->role('lead')->email('cellog@php.net')->active('yes')->name('Greg Beaver');
        $pf->setPackagefile($this->__DIR__ . '/package.xml');
        return $pf;
    }

    function makePackage(\pear2\Pyrus\PackageFile\v2 $pf)
    {
        foreach ($pf->files as $name => $blah) {
            file_put_contents($this->__DIR__ . '/' . $name, 'hi');
        }
        file_put_contents($this->__DIR__ . '/package.xml', $pf);
        $package1 = new \pear2\Pyrus\Package(false);
        $xmlcontainer = new \pear2\Pyrus\PackageFile($pf);
        $xml = new \pear2\Pyrus\Package\Xml($this->__DIR__ . '/package.xml', $package1, $xmlcontainer);
        $package1->setInternalPackage($xml);
        $package1->archivefile = $this->__DIR__ . '/package.xml';
        $this->scs->saveRelease($package1, 'cellog');
        foreach ($pf->files as $name => $blah) {
            unlink($this->__DIR__ . '/' . $name);
        }
        unlink($this->__DIR__ . '/package.xml');
    }

    function __destruct()
    {
        // clean up
        foreach (array(dirname($this->__DIR__) . '/pearconfig.xml',
                       dirname($this->__DIR__) . '/.config',
                       dirname($this->__DIR__) . '/.pear2registry') as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        foreach (array(dirname($this->__DIR__) . '/.configsnapshots',
                       dirname($this->__DIR__) . '/.xmlregistry',
                       dirname($this->__DIR__) . '/PEAR2/.xmlregistry',
                       dirname($this->__DIR__) . '/PEAR2/temp',
                       dirname($this->__DIR__) . '/temp'
                       ) as $dir) {
            include __DIR__ . '/../../clean.php.inc';
        }
    }
}
