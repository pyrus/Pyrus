<?php
namespace pear2\Pyrus\Developer\PackageFile;
class Commands
{
    function makePackageXml($args, $options)
    {
        if (isset($args['dir'])) {
            $dir = $args['dir'];
            if (!file_exists($dir)) {
                throw new Exception('Invalid directory: ' . $dir . ' does not exist');
            }
        } else {
            $dir = getcwd();
        }
        echo "Creating package.xml...";
        new \pear2\Pyrus\Developer\PackageFile\PEAR2SVN($dir, $args['packagename']);
        echo "done\n";
    }

    function makePECLPackage($args, $options)
    {
        if (isset($args['dir'])) {
            $dir = $args['dir'];
            if (!file_exists($dir)) {
                throw new Exception('Invalid directory: ' . $dir . ' does not exist');
            }
        } else {
            $dir = getcwd();
        }
        echo "Creating package.xml...";
        new \pear2\Pyrus\Developer\PackageFile\PEAR2SVN($dir, $args['packagename']);
        echo "done\n";
    }
}