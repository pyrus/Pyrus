--TEST--
packagefile parser for package.xml 2.0
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$pxml = dirname(__FILE__) . '/packages/package2.xml';
$ret = $parser->parse(file_get_contents($pxml), $pxml, 'Mockv2');
$test->assertEquals(array (
  'filelist' => 
  array (
    'OS/Guess.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'OS/Guess.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/ChannelFile/Parser.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/ChannelFile/Parser.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Auth.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Auth.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Auth.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Auth.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Build.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Build.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Build.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Build.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Channels.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Channels.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Channels.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Channels.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Common.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Common.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Config.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Config.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Config.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Config.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Install.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Install.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Install.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Install.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Mirror.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Mirror.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Mirror.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Mirror.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Package.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Package.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Package.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Package.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'from' => '@DATA-DIR@',
            'to' => 'data_dir',
            'type' => 'pear-config',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'from' => '@package_version@',
            'to' => 'version',
            'type' => 'package-info',
          ),
        ),
      ),
    ),
    'PEAR/Command/Pickle.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Pickle.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Pickle.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Pickle.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Registry.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Registry.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Registry.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Registry.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Remote.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Remote.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Remote.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Remote.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command/Test.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Test.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Command/Test.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command/Test.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Downloader/Package.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Downloader/Package.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@PEAR-VER@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Frontend/CLI.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Frontend/CLI.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer/Role/Common.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Common.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer/Role/Data.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Data.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Installer/Role/Data.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Data.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer/Role/Doc.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Doc.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Installer/Role/Doc.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Doc.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer/Role/Ext.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Ext.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Installer/Role/Ext.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Ext.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer/Role/Php.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Php.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Installer/Role/Php.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Php.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer/Role/Script.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Script.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Installer/Role/Script.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Script.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer/Role/Src.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Src.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Installer/Role/Src.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Src.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer/Role/Test.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Test.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Installer/Role/Test.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Test.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer/Role/Www.xml' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Www.xml',
        'role' => 'php',
      ),
    ),
    'PEAR/Installer/Role/Www.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role/Www.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer/Role.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer/Role.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/PackageFile/Generator/v1.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/PackageFile/Generator/v1.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@PEAR-VER@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/PackageFile/Generator/v2.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/PackageFile/Generator/v2.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@PEAR-VER@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/PackageFile/Parser/v1.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/PackageFile/Parser/v1.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/PackageFile/Parser/v2.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/PackageFile/Parser/v2.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/PackageFile/v2/rw.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'PEAR/PackageFile/v2/rw.php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/PackageFile/v2/Validator.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'PEAR/PackageFile/v2/Validator.php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/PackageFile/v1.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/PackageFile/v1.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/PackageFile/v2.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/PackageFile/v2.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/REST/10.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/REST/10.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/REST/11.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/REST/11.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/REST/13.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/REST/13.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Task/Postinstallscript/rw.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Task/Postinstallscript/rw.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Task/Replace/rw.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Task/Replace/rw.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Task/Unixeol/rw.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Task/Unixeol/rw.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Task/Windowseol/rw.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Task/Windowseol/rw.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Task/Common.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Task/Common.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Task/Postinstallscript.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Task/Postinstallscript.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Task/Replace.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Task/Replace.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Task/Unixeol.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Task/Unixeol.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Task/Windowseol.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Task/Windowseol.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Validator/PECL.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Validator/PECL.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Autoloader.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Autoloader.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Builder.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Builder.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@PEAR-VER@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/ChannelFile.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/ChannelFile.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Command.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Command.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Common.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Common.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Config.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Config.php',
         'role' => 'php',
       ),
       'tasks:replace' => 
       array (
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Dependency.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Dependency.php',
        'role' => 'php',
      ),
    ),
    'PEAR/DependencyDB.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/DependencyDB.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Dependency2.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Dependency2.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@PEAR-VER@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Downloader.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Downloader.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/ErrorStack.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/ErrorStack.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Exception.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Exception.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Frontend.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Frontend.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Installer.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Installer.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/PackageFile.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/PackageFile.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@PEAR-VER@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Packager.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Packager.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Registry.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Registry.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Remote.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Remote.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/REST.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/REST.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/RunTest.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/RunTest.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/Validate.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/Validate.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'PEAR/XMLParser.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR/XMLParser.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'scripts/pear.bat' => 
    array (
      'attribs' => 
      array (
        'name' => 'scripts/pear.bat',
        'role' => 'script',
      ),
      'tasks:replace' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'from' => '@bin_dir@',
            'to' => 'bin_dir',
            'type' => 'pear-config',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_bin@',
            'to' => 'php_bin',
            'type' => 'pear-config',
          ),
        ),
        2 => 
        array (
          'attribs' => 
          array (
            'from' => '@include_path@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
      ),
      'tasks:windowseol' => '',
    ),
    'scripts/peardev.bat' => 
    array (
      'attribs' => 
      array (
        'name' => 'scripts/peardev.bat',
        'role' => 'script',
      ),
      'tasks:replace' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'from' => '@bin_dir@',
            'to' => 'bin_dir',
            'type' => 'pear-config',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_bin@',
            'to' => 'php_bin',
            'type' => 'pear-config',
          ),
        ),
        2 => 
        array (
          'attribs' => 
          array (
            'from' => '@include_path@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
      ),
      'tasks:windowseol' => '',
    ),
    'scripts/pecl.bat' => 
    array (
      'attribs' => 
      array (
        'name' => 'scripts/pecl.bat',
        'role' => 'script',
      ),
      'tasks:replace' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'from' => '@bin_dir@',
            'to' => 'bin_dir',
            'type' => 'pear-config',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_bin@',
            'to' => 'php_bin',
            'type' => 'pear-config',
          ),
        ),
        2 => 
        array (
          'attribs' => 
          array (
            'from' => '@include_path@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
      ),
      'tasks:windowseol' => '',
    ),
    'scripts/pear.sh' => 
    array (
      'attribs' => 
      array (
        'name' => 'scripts/pear.sh',
        'role' => 'script',
      ),
      'tasks:replace' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_bin@',
            'to' => 'php_bin',
            'type' => 'pear-config',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_dir@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
        2 => 
        array (
          'attribs' => 
          array (
            'from' => '@pear_version@',
            'to' => 'version',
            'type' => 'package-info',
          ),
        ),
        3 => 
        array (
          'attribs' => 
          array (
            'from' => '@include_path@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
      ),
      'tasks:unixeol' => '',
    ),
    'scripts/peardev.sh' => 
    array (
      'attribs' => 
      array (
        'name' => 'scripts/peardev.sh',
        'role' => 'script',
      ),
      'tasks:replace' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_bin@',
            'to' => 'php_bin',
            'type' => 'pear-config',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_dir@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
        2 => 
        array (
          'attribs' => 
          array (
            'from' => '@pear_version@',
            'to' => 'version',
            'type' => 'package-info',
          ),
        ),
        3 => 
        array (
          'attribs' => 
          array (
            'from' => '@include_path@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
      ),
      'tasks:unixeol' => '',
    ),
    'scripts/pecl.sh' => 
    array (
      'attribs' => 
      array (
        'name' => 'scripts/pecl.sh',
        'role' => 'script',
      ),
      'tasks:replace' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_bin@',
            'to' => 'php_bin',
            'type' => 'pear-config',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_dir@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
        2 => 
        array (
          'attribs' => 
          array (
            'from' => '@pear_version@',
            'to' => 'version',
            'type' => 'package-info',
          ),
        ),
        3 => 
        array (
          'attribs' => 
          array (
            'from' => '@include_path@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
      ),
      'tasks:unixeol' => '',
    ),
    'scripts/pearcmd.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'scripts/pearcmd.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_bin@',
            'to' => 'php_bin',
            'type' => 'pear-config',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_dir@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
        2 => 
        array (
          'attribs' => 
          array (
            'from' => '@pear_version@',
            'to' => 'version',
            'type' => 'package-info',
          ),
        ),
        3 => 
        array (
          'attribs' => 
          array (
            'from' => '@include_path@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
      ),
    ),
    'scripts/peclcmd.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'scripts/peclcmd.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_bin@',
            'to' => 'php_bin',
            'type' => 'pear-config',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'from' => '@php_dir@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
        2 => 
        array (
          'attribs' => 
          array (
            'from' => '@pear_version@',
            'to' => 'version',
            'type' => 'package-info',
          ),
        ),
        3 => 
        array (
          'attribs' => 
          array (
            'from' => '@include_path@',
            'to' => 'php_dir',
            'type' => 'pear-config',
          ),
        ),
      ),
    ),
    'INSTALL' => 
    array (
      'attribs' => 
      array (
        'name' => 'INSTALL',
        'role' => 'doc',
      ),
    ),
    'package.dtd' => 
    array (
      'attribs' => 
      array (
        'name' => 'package.dtd',
        'role' => 'data',
      ),
    ),
    'PEAR.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'PEAR.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'README' => 
    array (
      'attribs' => 
      array (
        'name' => 'README',
        'role' => 'doc',
      ),
    ),
    'System.php' => 
    array (
      'attribs' => 
      array (
        'name' => 'System.php',
        'role' => 'php',
      ),
      'tasks:replace' => 
      array (
        'attribs' => 
        array (
          'from' => '@package_version@',
          'to' => 'version',
          'type' => 'package-info',
        ),
      ),
    ),
    'template.spec' => 
    array (
      'attribs' => 
      array (
        'name' => 'template.spec',
        'role' => 'data',
      ),
    ),
  ),
  'baseinstalls' => 
  array (
    'scripts' => '/',
  ),
  'packagefile' => dirname(__FILE__) . '/packages/package2.xml',
  'packageinfo' => 
  array (
    'attribs' => 
    array (
      'xmlns' => 'http://pear.php.net/dtd/package-2.0',
      'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
      'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
      'version' => '2.0',
      'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd',
    ),
    'name' => 'PEAR',
    'channel' => 'pear.php.net',
    'summary' => 'PEAR Base System',
    'description' => 'The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the PEAR_Exception PHP5 error handling mechanism
 * the PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class

  Features in a nutshell:
  * full support for channels
  * pre-download dependency validation
  * new package.xml 2.0 format allows tremendous flexibility while maintaining BC
  * support for optional dependency groups and limited support for sub-packaging
  * robust dependency support
  * full dependency validation on uninstall
  * remote install for hosts with only ftp access - no more problems with
    restricted host installation
  * full support for mirroring
  * support for bundling several packages into a single tarball
  * support for static dependencies on a url-based package
  * support for custom file roles and installation tasks
 ',
    'lead' => 
    array (
      0 => 
      array (
        'name' => 'Greg Beaver',
        'user' => 'cellog',
        'email' => 'cellog@php.net',
        'active' => 'yes',
      ),
      1 => 
      array (
        'name' => 'Pierre-Alain Joye',
        'user' => 'pajoye',
        'email' => 'pierre@php.net',
        'active' => 'no',
      ),
      2 => 
      array (
        'name' => 'Stig Bakken',
        'user' => 'ssb',
        'email' => 'stig@php.net',
        'active' => 'no',
      ),
      3 => 
      array (
        'name' => 'Tomas V.V.Cox',
        'user' => 'cox',
        'email' => 'cox@idecnet.com',
        'active' => 'no',
      ),
    ),
    'developer' => 
    array (
      0 => 
      array (
        'name' => 'Helgi Thormar',
        'user' => 'dufuz',
        'email' => 'dufuz@php.net',
        'active' => 'yes',
      ),
      1 => 
      array (
        'name' => 'Tias Guns',
        'user' => 'tias',
        'email' => 'tias@php.net',
        'active' => 'yes',
      ),
    ),
    'helper' => 
    array (
      0 => 
      array (
        'name' => 'Tim Jackson',
        'user' => 'timj',
        'email' => 'timj@php.net',
        'active' => 'no',
      ),
      1 => 
      array (
        'name' => 'Bertrand Gugger',
        'user' => 'toggg',
        'email' => 'toggg@php.net',
        'active' => 'no',
      ),
      2 => 
      array (
        'name' => 'Martin Jansen',
        'user' => 'mj',
        'email' => 'mj@php.net',
        'active' => 'no',
      ),
    ),
    'date' => '2007-10-01',
    'version' => 
    array (
      'release' => '1.7.0',
      'api' => '1.7.0',
    ),
    'stability' => 
    array (
      'release' => 'stable',
      'api' => 'stable',
    ),
    'license' => 
    array (
      'attribs' => 
      array (
        'uri' => 'http://www.php.net/license',
      ),
      '_content' => 'PHP License',
    ),
    'notes' => '
  * implement Request #11964: introduce www role, www_dir config variable [cellog]
 ',
    'contents' => 
    array (
      'dir' => 
      array (
        'attribs' => 
        array (
          'name' => '/',
        ),
        'dir' => 
        array (
          0 => 
          array (
            'attribs' => 
            array (
              'name' => 'OS',
            ),
            'file' => 
            array (
              'attribs' => 
              array (
                'name' => 'Guess.php',
                'role' => 'php',
              ),
              'tasks:replace' => 
              array (
                'attribs' => 
                array (
                  'from' => '@package_version@',
                  'to' => 'version',
                  'type' => 'package-info',
                ),
              ),
            ),
          ),
          1 => 
          array (
            'attribs' => 
            array (
              'name' => 'PEAR',
            ),
            'dir' => 
            array (
              0 => 
              array (
                'attribs' => 
                array (
                  'name' => 'ChannelFile',
                ),
                'file' => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Parser.php',
                    'role' => 'php',
                  ),
                  'tasks:replace' => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@package_version@',
                      'to' => 'version',
                      'type' => 'package-info',
                    ),
                  ),
                ),
              ),
              1 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Command',
                ),
                'file' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Auth.xml',
                      'role' => 'php',
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Auth.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Build.xml',
                      'role' => 'php',
                    ),
                  ),
                  3 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Build.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  4 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Channels.xml',
                      'role' => 'php',
                    ),
                  ),
                  5 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Channels.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  6 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Common.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  7 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Config.xml',
                      'role' => 'php',
                    ),
                  ),
                  8 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Config.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  9 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Install.xml',
                      'role' => 'php',
                    ),
                  ),
                  10 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Install.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  11 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Mirror.xml',
                      'role' => 'php',
                    ),
                  ),
                  12 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Mirror.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  13 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Package.xml',
                      'role' => 'php',
                    ),
                  ),
                  14 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Package.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      0 => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@DATA-DIR@',
                          'to' => 'data_dir',
                          'type' => 'pear-config',
                        ),
                      ),
                      1 => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                  ),
                  15 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Pickle.xml',
                      'role' => 'php',
                    ),
                  ),
                  16 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Pickle.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  17 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Registry.xml',
                      'role' => 'php',
                    ),
                  ),
                  18 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Registry.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  19 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Remote.xml',
                      'role' => 'php',
                    ),
                  ),
                  20 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Remote.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  21 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Test.xml',
                      'role' => 'php',
                    ),
                  ),
                  22 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Test.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                ),
              ),
              2 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Downloader',
                ),
                'file' => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Package.php',
                    'role' => 'php',
                  ),
                  'tasks:replace' => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@PEAR-VER@',
                      'to' => 'version',
                      'type' => 'package-info',
                    ),
                  ),
                ),
              ),
              3 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Frontend',
                ),
                'file' => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'CLI.php',
                    'role' => 'php',
                  ),
                  'tasks:replace' => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@package_version@',
                      'to' => 'version',
                      'type' => 'package-info',
                    ),
                  ),
                ),
              ),
              4 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Installer',
                ),
                'dir' => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Role',
                  ),
                  'file' => 
                  array (
                    0 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Common.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Data.xml',
                        'role' => 'php',
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Data.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                    3 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Doc.xml',
                        'role' => 'php',
                      ),
                    ),
                    4 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Doc.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                    5 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Ext.xml',
                        'role' => 'php',
                      ),
                    ),
                    6 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Ext.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                    7 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Php.xml',
                        'role' => 'php',
                      ),
                    ),
                    8 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Php.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                    9 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Script.xml',
                        'role' => 'php',
                      ),
                    ),
                    10 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Script.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                    11 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Src.xml',
                        'role' => 'php',
                      ),
                    ),
                    12 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Src.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                    13 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Test.xml',
                        'role' => 'php',
                      ),
                    ),
                    14 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Test.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                    15 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Www.xml',
                        'role' => 'php',
                      ),
                    ),
                    16 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Www.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                  ),
                ),
                'file' => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Role.php',
                    'role' => 'php',
                  ),
                  'tasks:replace' => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@package_version@',
                      'to' => 'version',
                      'type' => 'package-info',
                    ),
                  ),
                ),
              ),
              5 => 
              array (
                'attribs' => 
                array (
                  'name' => 'PackageFile',
                ),
                'dir' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Generator',
                    ),
                    'file' => 
                    array (
                      0 => 
                      array (
                        'attribs' => 
                        array (
                          'name' => 'v1.php',
                          'role' => 'php',
                        ),
                        'tasks:replace' => 
                        array (
                          'attribs' => 
                          array (
                            'from' => '@PEAR-VER@',
                            'to' => 'version',
                            'type' => 'package-info',
                          ),
                        ),
                      ),
                      1 => 
                      array (
                        'attribs' => 
                        array (
                          'name' => 'v2.php',
                          'role' => 'php',
                        ),
                        'tasks:replace' => 
                        array (
                          'attribs' => 
                          array (
                            'from' => '@PEAR-VER@',
                            'to' => 'version',
                            'type' => 'package-info',
                          ),
                        ),
                      ),
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Parser',
                    ),
                    'file' => 
                    array (
                      0 => 
                      array (
                        'attribs' => 
                        array (
                          'name' => 'v1.php',
                          'role' => 'php',
                        ),
                        'tasks:replace' => 
                        array (
                          'attribs' => 
                          array (
                            'from' => '@package_version@',
                            'to' => 'version',
                            'type' => 'package-info',
                          ),
                        ),
                      ),
                      1 => 
                      array (
                        'attribs' => 
                        array (
                          'name' => 'v2.php',
                          'role' => 'php',
                        ),
                        'tasks:replace' => 
                        array (
                          'attribs' => 
                          array (
                            'from' => '@package_version@',
                            'to' => 'version',
                            'type' => 'package-info',
                          ),
                        ),
                      ),
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'v2',
                    ),
                    'file' => 
                    array (
                      0 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'rw.php',
                        ),
                        'tasks:replace' => 
                        array (
                          'attribs' => 
                          array (
                            'from' => '@package_version@',
                            'to' => 'version',
                            'type' => 'package-info',
                          ),
                        ),
                      ),
                      1 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Validator.php',
                        ),
                        'tasks:replace' => 
                        array (
                          'attribs' => 
                          array (
                            'from' => '@package_version@',
                            'to' => 'version',
                            'type' => 'package-info',
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
                'file' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'v1.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'v2.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                ),
              ),
              6 => 
              array (
                'attribs' => 
                array (
                  'name' => 'REST',
                ),
                'file' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => '10.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => '11.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => '13.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                ),
              ),
              7 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Task',
                ),
                'dir' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Postinstallscript',
                    ),
                    'file' => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'rw.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Replace',
                    ),
                    'file' => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'rw.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Unixeol',
                    ),
                    'file' => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'rw.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                  ),
                  3 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Windowseol',
                    ),
                    'file' => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'rw.php',
                        'role' => 'php',
                      ),
                      'tasks:replace' => 
                      array (
                        'attribs' => 
                        array (
                          'from' => '@package_version@',
                          'to' => 'version',
                          'type' => 'package-info',
                        ),
                      ),
                    ),
                  ),
                ),
                'file' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Common.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Postinstallscript.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Replace.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  3 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Unixeol.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                  4 => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Windowseol.php',
                      'role' => 'php',
                    ),
                    'tasks:replace' => 
                    array (
                      'attribs' => 
                      array (
                        'from' => '@package_version@',
                        'to' => 'version',
                        'type' => 'package-info',
                      ),
                    ),
                  ),
                ),
              ),
              8 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Validator',
                ),
                'file' => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'PECL.php',
                    'role' => 'php',
                  ),
                  'tasks:replace' => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@package_version@',
                      'to' => 'version',
                      'type' => 'package-info',
                    ),
                  ),
                ),
              ),
            ),
            'file' => 
            array (
              0 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Autoloader.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              1 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Builder.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@PEAR-VER@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              2 => 
              array (
                'attribs' => 
                array (
                  'name' => 'ChannelFile.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              3 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Command.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              4 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Common.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              5 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Config.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              6 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Dependency.php',
                  'role' => 'php',
                ),
              ),
              7 => 
              array (
                'attribs' => 
                array (
                  'name' => 'DependencyDB.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              8 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Dependency2.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@PEAR-VER@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              9 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Downloader.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              10 => 
              array (
                'attribs' => 
                array (
                  'name' => 'ErrorStack.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              11 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Exception.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              12 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Frontend.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              13 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Installer.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              14 => 
              array (
                'attribs' => 
                array (
                  'name' => 'PackageFile.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@PEAR-VER@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              15 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Packager.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              16 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Registry.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              17 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Remote.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              18 => 
              array (
                'attribs' => 
                array (
                  'name' => 'REST.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              19 => 
              array (
                'attribs' => 
                array (
                  'name' => 'RunTest.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              20 => 
              array (
                'attribs' => 
                array (
                  'name' => 'Validate.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
              21 => 
              array (
                'attribs' => 
                array (
                  'name' => 'XMLParser.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  'attribs' => 
                  array (
                    'from' => '@package_version@',
                    'to' => 'version',
                    'type' => 'package-info',
                  ),
                ),
              ),
            ),
          ),
          2 => 
          array (
            'attribs' => 
            array (
              'name' => 'scripts',
              'baseinstalldir' => '/',
            ),
            'file' => 
            array (
              0 => 
              array (
                'attribs' => 
                array (
                  'name' => 'pear.bat',
                  'role' => 'script',
                ),
                'tasks:replace' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@bin_dir@',
                      'to' => 'bin_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_bin@',
                      'to' => 'php_bin',
                      'type' => 'pear-config',
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@include_path@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                ),
                'tasks:windowseol' => '',
              ),
              1 => 
              array (
                'attribs' => 
                array (
                  'name' => 'peardev.bat',
                  'role' => 'script',
                ),
                'tasks:replace' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@bin_dir@',
                      'to' => 'bin_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_bin@',
                      'to' => 'php_bin',
                      'type' => 'pear-config',
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@include_path@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                ),
                'tasks:windowseol' => '',
              ),
              2 => 
              array (
                'attribs' => 
                array (
                  'name' => 'pecl.bat',
                  'role' => 'script',
                ),
                'tasks:replace' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@bin_dir@',
                      'to' => 'bin_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_bin@',
                      'to' => 'php_bin',
                      'type' => 'pear-config',
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@include_path@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                ),
                'tasks:windowseol' => '',
              ),
              3 => 
              array (
                'attribs' => 
                array (
                  'name' => 'pear.sh',
                  'role' => 'script',
                ),
                'tasks:replace' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_bin@',
                      'to' => 'php_bin',
                      'type' => 'pear-config',
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_dir@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@pear_version@',
                      'to' => 'version',
                      'type' => 'package-info',
                    ),
                  ),
                  3 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@include_path@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                ),
                'tasks:unixeol' => '',
              ),
              4 => 
              array (
                'attribs' => 
                array (
                  'name' => 'peardev.sh',
                  'role' => 'script',
                ),
                'tasks:replace' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_bin@',
                      'to' => 'php_bin',
                      'type' => 'pear-config',
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_dir@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@pear_version@',
                      'to' => 'version',
                      'type' => 'package-info',
                    ),
                  ),
                  3 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@include_path@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                ),
                'tasks:unixeol' => '',
              ),
              5 => 
              array (
                'attribs' => 
                array (
                  'name' => 'pecl.sh',
                  'role' => 'script',
                ),
                'tasks:replace' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_bin@',
                      'to' => 'php_bin',
                      'type' => 'pear-config',
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_dir@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@pear_version@',
                      'to' => 'version',
                      'type' => 'package-info',
                    ),
                  ),
                  3 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@include_path@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                ),
                'tasks:unixeol' => '',
              ),
              6 => 
              array (
                'attribs' => 
                array (
                  'name' => 'pearcmd.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_bin@',
                      'to' => 'php_bin',
                      'type' => 'pear-config',
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_dir@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@pear_version@',
                      'to' => 'version',
                      'type' => 'package-info',
                    ),
                  ),
                  3 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@include_path@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                ),
              ),
              7 => 
              array (
                'attribs' => 
                array (
                  'name' => 'peclcmd.php',
                  'role' => 'php',
                ),
                'tasks:replace' => 
                array (
                  0 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_bin@',
                      'to' => 'php_bin',
                      'type' => 'pear-config',
                    ),
                  ),
                  1 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@php_dir@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                  2 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@pear_version@',
                      'to' => 'version',
                      'type' => 'package-info',
                    ),
                  ),
                  3 => 
                  array (
                    'attribs' => 
                    array (
                      'from' => '@include_path@',
                      'to' => 'php_dir',
                      'type' => 'pear-config',
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
        'file' => 
        array (
          0 => 
          array (
            'attribs' => 
            array (
              'name' => 'INSTALL',
              'role' => 'doc',
            ),
          ),
          1 => 
          array (
            'attribs' => 
            array (
              'name' => 'package.dtd',
              'role' => 'data',
            ),
          ),
          2 => 
          array (
            'attribs' => 
            array (
              'name' => 'PEAR.php',
              'role' => 'php',
            ),
            'tasks:replace' => 
            array (
              'attribs' => 
              array (
                'from' => '@package_version@',
                'to' => 'version',
                'type' => 'package-info',
              ),
            ),
          ),
          3 => 
          array (
            'attribs' => 
            array (
              'name' => 'README',
              'role' => 'doc',
            ),
          ),
          4 => 
          array (
            'attribs' => 
            array (
              'name' => 'System.php',
              'role' => 'php',
            ),
            'tasks:replace' => 
            array (
              'attribs' => 
              array (
                'from' => '@package_version@',
                'to' => 'version',
                'type' => 'package-info',
              ),
            ),
          ),
          5 => 
          array (
            'attribs' => 
            array (
              'name' => 'template.spec',
              'role' => 'data',
            ),
          ),
        ),
      ),
    ),
    'dependencies' => 
    array (
      'required' => 
      array (
        'php' => 
        array (
          'min' => '4.3.0',
        ),
        'pearinstaller' => 
        array (
          'min' => '1.4.3',
        ),
        'package' => 
        array (
          0 => 
          array (
            'name' => 'Archive_Tar',
            'channel' => 'pear.php.net',
            'min' => '1.1',
            'recommended' => '1.3.2',
            'exclude' => '1.3.0',
          ),
          1 => 
          array (
            'name' => 'Structures_Graph',
            'channel' => 'pear.php.net',
            'min' => '1.0.2',
            'recommended' => '1.0.2',
          ),
          2 => 
          array (
            'name' => 'Console_Getopt',
            'channel' => 'pear.php.net',
            'min' => '1.2',
            'recommended' => '1.2.3',
          ),
          3 => 
          array (
            'name' => 'PEAR_Frontend_Web',
            'channel' => 'pear.php.net',
            'max' => '0.4',
            'conflicts' => '',
          ),
          4 => 
          array (
            'name' => 'PEAR_Frontend_Gtk',
            'channel' => 'pear.php.net',
            'max' => '0.4.0',
            'exclude' => '0.4.0',
            'conflicts' => '',
          ),
        ),
        'extension' => 
        array (
          0 => 
          array (
            'name' => 'xml',
          ),
          1 => 
          array (
            'name' => 'pcre',
          ),
        ),
      ),
      'optional' => 
      array (
        'package' => 
        array (
          'name' => 'XML_RPC',
          'channel' => 'pear.php.net',
          'min' => '1.4.0',
        ),
      ),
      'group' => 
      array (
        0 => 
        array (
          'attribs' => 
          array (
            'name' => 'webinstaller',
            'hint' => 'PEAR\'s web-based installer',
          ),
          'package' => 
          array (
            'name' => 'PEAR_Frontend_Web',
            'channel' => 'pear.php.net',
            'min' => '0.5.1',
          ),
        ),
        1 => 
        array (
          'attribs' => 
          array (
            'name' => 'gtkinstaller',
            'hint' => 'PEAR\'s PHP-GTK-based installer',
          ),
          'package' => 
          array (
            'name' => 'PEAR_Frontend_Gtk',
            'channel' => 'pear.php.net',
            'min' => '0.4.0',
          ),
        ),
        2 => 
        array (
          'attribs' => 
          array (
            'name' => 'gtk2installer',
            'hint' => 'PEAR\'s PHP-GTK2-based installer',
          ),
          'package' => 
          array (
            'name' => 'PEAR_Frontend_Gtk2',
            'channel' => 'pear.php.net',
          ),
        ),
      ),
    ),
    'phprelease' => 
    array (
      0 => 
      array (
        'installconditions' => 
        array (
          'os' => 
          array (
            'name' => 'windows',
          ),
        ),
        'filelist' => 
        array (
          'install' => 
          array (
            0 => 
            array (
              'attribs' => 
              array (
                'as' => 'pear.bat',
                'name' => 'scripts/pear.bat',
              ),
            ),
            1 => 
            array (
              'attribs' => 
              array (
                'as' => 'peardev.bat',
                'name' => 'scripts/peardev.bat',
              ),
            ),
            2 => 
            array (
              'attribs' => 
              array (
                'as' => 'pecl.bat',
                'name' => 'scripts/pecl.bat',
              ),
            ),
            3 => 
            array (
              'attribs' => 
              array (
                'as' => 'pearcmd.php',
                'name' => 'scripts/pearcmd.php',
              ),
            ),
            4 => 
            array (
              'attribs' => 
              array (
                'as' => 'peclcmd.php',
                'name' => 'scripts/peclcmd.php',
              ),
            ),
          ),
          'ignore' => 
          array (
            0 => 
            array (
              'attribs' => 
              array (
                'name' => 'scripts/peardev.sh',
              ),
            ),
            1 => 
            array (
              'attribs' => 
              array (
                'name' => 'scripts/pear.sh',
              ),
            ),
            2 => 
            array (
              'attribs' => 
              array (
                'name' => 'scripts/pecl.sh',
              ),
            ),
          ),
        ),
      ),
      1 => 
      array (
        'filelist' => 
        array (
          'install' => 
          array (
            0 => 
            array (
              'attribs' => 
              array (
                'as' => 'pear',
                'name' => 'scripts/pear.sh',
              ),
            ),
            1 => 
            array (
              'attribs' => 
              array (
                'as' => 'peardev',
                'name' => 'scripts/peardev.sh',
              ),
            ),
            2 => 
            array (
              'attribs' => 
              array (
                'as' => 'pecl',
                'name' => 'scripts/pecl.sh',
              ),
            ),
            3 => 
            array (
              'attribs' => 
              array (
                'as' => 'pearcmd.php',
                'name' => 'scripts/pearcmd.php',
              ),
            ),
            4 => 
            array (
              'attribs' => 
              array (
                'as' => 'peclcmd.php',
                'name' => 'scripts/peclcmd.php',
              ),
            ),
          ),
          'ignore' => 
          array (
            0 => 
            array (
              'attribs' => 
              array (
                'name' => 'scripts/pear.bat',
              ),
            ),
            1 => 
            array (
              'attribs' => 
              array (
                'name' => 'scripts/peardev.bat',
              ),
            ),
            2 => 
            array (
              'attribs' => 
              array (
                'name' => 'scripts/pecl.bat',
              ),
            ),
          ),
        ),
      ),
    ),
    'changelog' => 
    array (
      'release' => 
      array (
        'version' => 
        array (
          'release' => '1.6.2',
          'api' => '1.6.0',
        ),
        'stability' => 
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2007-09-09',
        'license' => 
        array (
          'attribs' => 
          array (
            'uri' => 'http://www.php.net/license',
          ),
          '_content' => 'PHP License',
        ),
        'notes' => '
  Minor bugfix release
  * fix Bug #11420: warning on pecl (un)install with --register-only option [cellog]
  * fix Bug #11481: PEAR_PackageFile_Parser_v1 skips single-char directories [pmjones]
  * fix Bug #11517: Error : download directory "/var/cache/php-pear"
    is not writeable. [remicollet]
  * fix Bug #11616: Incorrect equality operator used when comparing md5 check sums [robham]
  * fix Bug #11642: PEAR fails to authenticate when downloading deps from non-default
    channels [timj]
  * fix Bug #11657: Installer generate bad "dirtree" using INSTALL_ROOT [remicollet]
  * fix Bug #11678: Registry.php getChannel() deadlocks [cellog]
  * fix Bug #11703: pear convert and package.xml with optional dependencies fails [cellog]
  * fix Bug #11754: Error at upgrade-all command run [cellog]
  * fix Bug #11861: uninstall of package did not delete directory created during install
    of package [cellog]
  * fix Bug #11862: Notice: Array to string conversion in PEAR/PackageFile.php on line 433
    [cellog]
  * fix Bug #11883: run-tests -u -p SomePackage should run the topmost
    "AllTests.php" file [cellog]
  * fix Bug #11936: run-tests fails to preserve SYSTEMROOT environment variable [cellog]
   ',
      ),
    ),
  ),
), $ret->getThingy(), 'test');
?>
===DONE===
--EXPECT--
===DONE===