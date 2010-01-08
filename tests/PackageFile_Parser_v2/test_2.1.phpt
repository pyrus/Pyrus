--TEST--
packagefile parser for package.xml 2.1
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$pxml = dirname(__FILE__) . '/packages/package.xml';
$ret = $parser->parse(file_get_contents($pxml), $pxml, 'Mockv2');
$test->assertEquals(array (
  'filelist' => 
  array (
    'data/channel-1.0.xsd' => 
    array (
      'attribs' => 
      array (
        'role' => 'data',
        'name' => 'data/channel-1.0.xsd',
      ),
    ),
    'data/package-2.0.xsd' => 
    array (
      'attribs' => 
      array (
        'role' => 'data',
        'name' => 'data/package-2.0.xsd',
      ),
    ),
    'data/package-2.1.xsd' => 
    array (
      'attribs' => 
      array (
        'role' => 'data',
        'name' => 'data/package-2.1.xsd',
      ),
    ),
    'src/Pyrus/Channel/Base.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Channel/Base.php',
      ),
    ),
    'src/Pyrus/Channel/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Channel/Exception.php',
      ),
    ),
    'src/Pyrus/Channel/MirrorInterface.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Channel/MirrorInterface.php',
      ),
    ),
    'src/Pyrus/Channel/Mirror.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Channel/Mirror.php',
      ),
    ),
    'src/Pyrus/ChannelRegistry/Channel/Sqlite.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistry/Channel/Sqlite.php',
      ),
    ),
    'src/Pyrus/ChannelRegistry/Channel/Xml.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistry/Channel/Xml.php',
      ),
    ),
    'src/Pyrus/ChannelRegistry/Mirror/Sqlite.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistry/Mirror/Sqlite.php',
      ),
    ),
    'src/Pyrus/ChannelRegistry/Mirror/Xml.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistry/Mirror/Xml.php',
      ),
    ),
    'src/Pyrus/ChannelRegistry/Channel/Xml.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistry/Channel/Xml.php',
      ),
    ),
    'src/Pyrus/ChannelRegistry/Base.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistry/Base.php',
      ),
    ),
    'src/Pyrus/ChannelRegistry/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistry/Exception.php',
      ),
    ),
    'src/Pyrus/ChannelRegistry/Sqlite.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistry/Sqlite.php',
      ),
    ),
    'src/Pyrus/ChannelRegistry/Xml.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistry/Xml.php',
      ),
    ),
    'src/Pyrus/Config/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Config/Exception.php',
      ),
    ),
    'src/Pyrus/Dependency/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Dependency/Exception.php',
      ),
    ),
    'src/Pyrus/Dependency/Validator.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Dependency/Validator.php',
      ),
    ),
    'src/Pyrus/DirectedGraph/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/DirectedGraph/Exception.php',
      ),
    ),
    'src/Pyrus/DirectedGraph/Vertex.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/DirectedGraph/Vertex.php',
      ),
    ),
    'src/Pyrus/FileTransactions/Installedas.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/FileTransactions/Installedas.php',
      ),
    ),
    'src/Pyrus/FileTransactions/Rename.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/FileTransactions/Rename.php',
      ),
    ),
    'src/Pyrus/FileTransactions/Rmdir.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/FileTransactions/Rmdir.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Common.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Common.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Data.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Data.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Data.xml' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Data.xml',
      ),
    ),
    'src/Pyrus/Installer/Role/Doc.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Doc.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Doc.xml' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Doc.xml',
      ),
    ),
    'src/Pyrus/Installer/Role/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Exception.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Ext.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Ext.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Ext.xml' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Ext.xml',
      ),
    ),
    'src/Pyrus/Installer/Role/Php.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Php.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Php.xml' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Php.xml',
      ),
    ),
    'src/Pyrus/Installer/Role/Script.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Script.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Script.xml' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Script.xml',
      ),
    ),
    'src/Pyrus/Installer/Role/Src.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Src.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Src.xml' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Src.xml',
      ),
    ),
    'src/Pyrus/Installer/Role/Test.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Test.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Test.xml' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Test.xml',
      ),
    ),
    'src/Pyrus/Installer/Role/Www.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Www.php',
      ),
    ),
    'src/Pyrus/Installer/Role/Www.xml' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role/Www.xml',
      ),
    ),
    'src/Pyrus/Installer/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Exception.php',
      ),
    ),
    'src/Pyrus/Installer/Role.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer/Role.php',
      ),
    ),
    'src/Pyrus/Package/Creator/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Creator/Exception.php',
      ),
    ),
    'src/Pyrus/Package/Creator/TaskIterator.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Creator/TaskIterator.php',
      ),
    ),
    'src/Pyrus/Package/Phar/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Phar/Exception.php',
      ),
    ),
    'src/Pyrus/Package/Tar/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Tar/Exception.php',
      ),
    ),
    'src/Pyrus/Package/Zip/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Zip/Exception.php',
      ),
    ),
    'src/Pyrus/Package/Base.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Base.php',
      ),
    ),
    'src/Pyrus/Package/Creator.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Creator.php',
      ),
    ),
    'src/Pyrus/Package/Dependency.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Dependency.php',
      ),
    ),
    'src/Pyrus/Package/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Exception.php',
      ),
    ),
    'src/Pyrus/Package/CreatorInterface.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/CreatorInterface.php',
      ),
    ),
    'src/Pyrus/Package/InstalledException.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/InstalledException.php',
      ),
    ),
    'src/Pyrus/Package/Phar.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Phar.php',
      ),
    ),
    'src/Pyrus/Package/Remote.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Remote.php',
      ),
    ),
    'src/Pyrus/Package/Tar.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Tar.php',
      ),
    ),
    'src/Pyrus/Package/Xml.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Xml.php',
      ),
    ),
    'src/Pyrus/Package/Zip.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package/Zip.php',
      ),
    ),
    'src/Pyrus/PackageFile/Parser/v2.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/Parser/v2.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Compatible/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Compatible/Exception.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Dependencies/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Dependencies/Exception.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Developer/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Developer/Exception.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Files/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Files/Exception.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Release/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Release/Exception.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Compatible.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Compatible.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Dependencies.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Dependencies.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Developer.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Developer.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Files.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Files.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Release.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Release.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Remote.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Remote.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2/Validator.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2/Validator.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2Iterator/File.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2Iterator/File.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2Iterator/FileAttribsFilter.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2Iterator/FileAttribsFilter.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2Iterator/FileContents.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2Iterator/FileContents.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2Iterator/FileContentsMulti.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2Iterator/FileContentsMulti.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2Iterator/FileInstallationFilter.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2Iterator/FileInstallationFilter.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2Iterator/FileTag.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2Iterator/FileTag.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2Iterator/PackagingIterator.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2Iterator/PackagingIterator.php',
      ),
    ),
    'src/Pyrus/PackageFile/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/Exception.php',
      ),
    ),
    'src/Pyrus/PackageFile/ValidatorInterface.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/ValidatorInterface.php',
      ),
    ),
    'src/Pyrus/PackageFile/v2.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile/v2.php',
      ),
    ),
    'src/Pyrus/Registry/Sqlite/Channel/Mirror.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Sqlite/Channel/Mirror.php',
      ),
    ),
    'src/Pyrus/Registry/Sqlite/Channel/Mirrors.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Sqlite/Channel/Mirrors.php',
      ),
    ),
    'src/Pyrus/Registry/Sqlite/Creator.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Sqlite/Creator.php',
      ),
    ),
    'src/Pyrus/Registry/Sqlite/Package.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Sqlite/Package.php',
      ),
    ),
    'src/Pyrus/Registry/Base.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Base.php',
      ),
    ),
    'src/Pyrus/Registry/Channel.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Channel.php',
      ),
    ),
    'src/Pyrus/Registry/Config.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Config.php',
      ),
    ),
    'src/Pyrus/Registry/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Exception.php',
      ),
    ),
    'src/Pyrus/Registry/Package.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Package.php',
      ),
    ),
    'src/Pyrus/Registry/Sqlite.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Sqlite.php',
      ),
    ),
    'src/Pyrus/Registry/Xml.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry/Xml.php',
      ),
    ),
    'src/Pyrus/REST/10.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/REST/10.php',
      ),
    ),
    'src/Pyrus/REST/11.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/REST/11.php',
      ),
    ),
    'src/Pyrus/REST/13.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/REST/13.php',
      ),
    ),
    'src/Pyrus/REST/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/REST/Exception.php',
      ),
    ),
    'src/Pyrus/REST/HTTPException.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/REST/HTTPException.php',
      ),
    ),
    'src/Pyrus/ScriptFrontend/Commands.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ScriptFrontend/Commands.php',
      ),
    ),
    'src/Pyrus/Task/Postinstallscript/rw.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Task/Postinstallscript/rw.php',
      ),
    ),
    'src/Pyrus/Task/Replace/rw.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Task/Replace/rw.php',
      ),
    ),
    'src/Pyrus/Task/Unixeol/rw.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Task/Unixeol/rw.php',
      ),
    ),
    'src/Pyrus/Task/Windowseol/rw.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Task/Windowseol/rw.php',
      ),
    ),
    'src/Pyrus/Task/Common.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Task/Common.php',
      ),
    ),
    'src/Pyrus/Task/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Task/Exception.php',
      ),
    ),
    'src/Pyrus/Task/Postinstallscript.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Task/Postinstallscript.php',
      ),
    ),
    'src/Pyrus/Task/Replace.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Task/Replace.php',
      ),
    ),
    'src/Pyrus/Task/Unixeol.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Task/Unixeol.php',
      ),
    ),
    'src/Pyrus/Task/Windowseol.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Task/Windowseol.php',
      ),
    ),
    'src/Pyrus/Validate/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Validate/Exception.php',
      ),
    ),
    'src/Pyrus/Validator/PECL.php' => 
     array (
       'attribs' => 
       array (
         'role' => 'php',
         'name' => 'src/Pyrus/Validator/PECL.php',
      ),
    ),
    'src/Pyrus/XMLParser/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/XMLParser/Exception.php',
      ),
    ),
    'src/Pyrus/XMLWriter/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/XMLWriter/Exception.php',
      ),
    ),
    'src/Pyrus/Channel.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Channel.php',
      ),
    ),
    'src/Pyrus/ChannelRegistry.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistry.php',
      ),
    ),
    'src/Pyrus/Config.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Config.php',
      ),
    ),
    'src/Pyrus/DirectedGraph.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/DirectedGraph.php',
      ),
    ),
    'src/Pyrus/Downloader.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Downloader.php',
      ),
    ),
    'src/Pyrus/Exception.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Exception.php',
      ),
    ),
    'src/Pyrus/FileTransactions.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/FileTransactions.php',
      ),
    ),
    'src/Pyrus/ChannelInterface.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelInterface.php',
      ),
    ),
    'src/Pyrus/ChannelRegistryInterface.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/ChannelRegistryInterface.php',
      ),
    ),
    'src/Pyrus/FileTransactionInterface.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/FileTransactionInterface.php',
      ),
    ),
    'src/Pyrus/Installer.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Installer.php',
      ),
    ),
    'src/Pyrus/PackageInterface.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageInterface.php',
      ),
    ),
    'src/Pyrus/RegistryInterface.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/RegistryInterface.php',
      ),
    ),
    'src/Pyrus/Log.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Log.php',
      ),
    ),
    'src/Pyrus/OSGuess.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/OSGuess.php',
      ),
    ),
    'src/Pyrus/Package.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Package.php',
      ),
    ),
    'src/Pyrus/PackageFile.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/PackageFile.php',
      ),
    ),
    'src/Pyrus/Registry.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Registry.php',
      ),
    ),
    'src/Pyrus/REST.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/REST.php',
      ),
    ),
    'src/Pyrus/Validate.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/Validate.php',
      ),
    ),
    'src/Pyrus/XMLParser.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/XMLParser.php',
      ),
    ),
    'src/Pyrus/XMLWriter.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus/XMLWriter.php',
      ),
    ),
    'src/Pyrus.php' => 
    array (
      'attribs' => 
      array (
        'role' => 'php',
        'name' => 'src/Pyrus.php',
      ),
    ),
  ),
  'baseinstalls' => 
  array (
    'data' => '/',
    'src' => 'PEAR2',
  ),
  'packagefile' => dirname(__FILE__) . '/packages/package.xml',
  'packageinfo' => 
  array (
    'attribs' => 
    array (
      'xmlns' => 'http://pear.php.net/dtd/package-2.1',
      'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
      'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
      'version' => '2.1',
      'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0     http://pear.php.net/dtd/tasks-1.0.xsd     http://pear.php.net/dtd/package-2.1     http://pear.php.net/dtd/package-2.1.xsd',
    ),
    'name' => 'PEAR2_Pyrus',
    'channel' => 'pear2.php.net',
    'summary' => 'Pyrus is the package manager and installer for PHP 5.2 or newer
',
    'description' => '
Pyrus provides the means to install and manage installations for
packages built using package.xml version 2.0 or newer.  Pyrus is
redesigned from the ground up for PHP 5.2 or newer, and provides
significant improvements over the older PEAR Installer.

To use Pyrus, in this development series of releases, you need to
instantiate a simple script that creates a package and installs it:

<?php
// use full path if include_path is not set up
include \'PEAR2/Autoload.php\';
require_once \'Net/URL2.php\'; // PEAR package needed for PEAR2_HTTP_Request dep
$config = PEAR2_Pyrus_Config::singleton(\'/where/to/install\');
// this can be a url to a remote package, a local .tgz, .zip or package.xml
$p = new PEAR2_Pyrus_Package(\'thingtoinstall\');
try {
    PEAR2_Pyrus_Installer::begin();
    PEAR2_Pyrus_Installer::prepare($p);
    PEAR2_Pyrus_Installer::commit();
} catch (Exception $e) {
    PEAR2_Pyrus_Installer::rollback();
    echo $e;
}
?>',
    'lead' => 
    array (
      'name' => 'Gregory Beaver',
      'user' => 'cellog',
      'email' => 'cellog@php.net',
      'active' => 'yes',
    ),
    'date' => '2007-10-06',
    'version' => 
    array (
      'release' => '2.0.0a1',
      'api' => '0.1.0',
    ),
    'stability' => 
    array (
      'release' => 'alpha',
      'api' => 'alpha',
    ),
    'license' => 
    array (
      'attribs' => 
      array (
        'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
      ),
      '_content' => 'New BSD License',
    ),
    'notes' => 'Initial development release, no frontend is included',
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
              'name' => 'data',
              'baseinstalldir' => '/',
            ),
            'file' => 
            array (
              0 => 
              array (
                'attribs' => 
                array (
                  'role' => 'data',
                  'name' => 'channel-1.0.xsd',
                ),
              ),
              1 => 
              array (
                'attribs' => 
                array (
                  'role' => 'data',
                  'name' => 'package-2.0.xsd',
                ),
              ),
              2 => 
              array (
                'attribs' => 
                array (
                  'role' => 'data',
                  'name' => 'package-2.1.xsd',
                ),
              ),
            ),
          ),
          1 => 
          array (
            'attribs' => 
            array (
              'name' => 'src',
              'baseinstalldir' => 'PEAR2',
            ),
            'dir' => 
            array (
              'attribs' => 
              array (
                'name' => 'Pyrus',
              ),
              'dir' => 
              array (
                0 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Channel',
                  ),
                  'file' => 
                  array (
                    0 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Base.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Exception.php',
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'MirrorInterface.php',
                      ),
                    ),
                    3 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Mirror.php',
                      ),
                    ),
                  ),
                ),
                1 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'ChannelRegistry',
                  ),
                  'dir' => 
                  array (
                    0 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Channel',
                      ),
                      'file' => 
                      array (
                        0 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Sqlite.php',
                          ),
                        ),
                        1 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Xml.php',
                          ),
                        ),
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Mirror',
                      ),
                      'file' => 
                      array (
                        0 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Sqlite.php',
                          ),
                        ),
                        1 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Xml.php',
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
                        'role' => 'php',
                        'name' => 'Base.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Exception.php',
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Sqlite.php',
                      ),
                    ),
                    3 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Xml.php',
                      ),
                    ),
                  ),
                ),
                2 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Config',
                  ),
                  'file' => 
                  array (
                    'attribs' => 
                    array (
                      'role' => 'php',
                      'name' => 'Exception.php',
                    ),
                  ),
                ),
                3 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Dependency',
                  ),
                  'file' => 
                  array (
                    0 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Exception.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Validator.php',
                      ),
                    ),
                  ),
                ),
                4 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'DirectedGraph',
                  ),
                  'file' => 
                  array (
                    0 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Exception.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Vertex.php',
                      ),
                    ),
                  ),
                ),
                5 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'FileTransactions',
                  ),
                  'file' => 
                  array (
                    0 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Installedas.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Rename.php',
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Rmdir.php',
                      ),
                    ),
                  ),
                ),
                6 => 
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
                          'role' => 'php',
                          'name' => 'Common.php',
                        ),
                      ),
                      1 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Data.php',
                        ),
                      ),
                      2 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Data.xml',
                        ),
                      ),
                      3 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Doc.php',
                        ),
                      ),
                      4 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Doc.xml',
                        ),
                      ),
                      5 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Exception.php',
                        ),
                      ),
                      6 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Ext.php',
                        ),
                      ),
                      7 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Ext.xml',
                        ),
                      ),
                      8 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Php.php',
                        ),
                      ),
                      9 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Php.xml',
                        ),
                      ),
                      10 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Script.php',
                        ),
                      ),
                      11 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Script.xml',
                        ),
                      ),
                      12 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Src.php',
                        ),
                      ),
                      13 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Src.xml',
                        ),
                      ),
                      14 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Test.php',
                        ),
                      ),
                      15 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Test.xml',
                        ),
                      ),
                      16 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Www.php',
                        ),
                      ),
                      17 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Www.xml',
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
                        'role' => 'php',
                        'name' => 'Exception.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Role.php',
                      ),
                    ),
                  ),
                ),
                7 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Package',
                  ),
                  'dir' => 
                  array (
                    0 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Creator',
                      ),
                      'file' => 
                      array (
                        0 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Exception.php',
                          ),
                        ),
                        1 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'TaskIterator.php',
                          ),
                        ),
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Phar',
                      ),
                      'file' => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Exception.php',
                        ),
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Tar',
                      ),
                      'file' => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Exception.php',
                        ),
                      ),
                    ),
                    3 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Zip',
                      ),
                      'file' => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Exception.php',
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
                        'role' => 'php',
                        'name' => 'Base.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Creator.php',
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Dependency.php',
                      ),
                    ),
                    3 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Exception.php',
                      ),
                    ),
                    4 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'CreatorInterface.php',
                      ),
                    ),
                    5 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'InstalledException.php',
                      ),
                    ),
                    6 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Phar.php',
                      ),
                    ),
                    7 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Remote.php',
                      ),
                    ),
                    8 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Tar.php',
                      ),
                    ),
                    9 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Xml.php',
                      ),
                    ),
                    10 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Zip.php',
                      ),
                    ),
                  ),
                ),
                8 => 
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
                        'name' => 'Parser',
                      ),
                      'file' => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'v2.php',
                        ),
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'v2',
                      ),
                      'dir' => 
                      array (
                        0 => 
                        array (
                          'attribs' => 
                          array (
                            'name' => 'Compatible',
                          ),
                          'file' => 
                          array (
                            'attribs' => 
                            array (
                              'role' => 'php',
                              'name' => 'Exception.php',
                            ),
                          ),
                        ),
                        1 => 
                        array (
                          'attribs' => 
                          array (
                            'name' => 'Dependencies',
                          ),
                          'file' => 
                          array (
                            'attribs' => 
                            array (
                              'role' => 'php',
                              'name' => 'Exception.php',
                            ),
                          ),
                        ),
                        2 => 
                        array (
                          'attribs' => 
                          array (
                            'name' => 'Developer',
                          ),
                          'file' => 
                          array (
                            'attribs' => 
                            array (
                              'role' => 'php',
                              'name' => 'Exception.php',
                            ),
                          ),
                        ),
                        3 => 
                        array (
                          'attribs' => 
                          array (
                            'name' => 'Files',
                          ),
                          'file' => 
                          array (
                            'attribs' => 
                            array (
                              'role' => 'php',
                              'name' => 'Exception.php',
                            ),
                          ),
                        ),
                        4 => 
                        array (
                          'attribs' => 
                          array (
                            'name' => 'Release',
                          ),
                          'file' => 
                          array (
                            'attribs' => 
                            array (
                              'role' => 'php',
                              'name' => 'Exception.php',
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
                            'role' => 'php',
                            'name' => 'Compatible.php',
                          ),
                        ),
                        1 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Dependencies.php',
                          ),
                        ),
                        2 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Developer.php',
                          ),
                        ),
                        3 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Files.php',
                          ),
                        ),
                        4 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Release.php',
                          ),
                        ),
                        5 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Remote.php',
                          ),
                        ),
                        6 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Validator.php',
                          ),
                        ),
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'v2Iterator',
                      ),
                      'file' => 
                      array (
                        0 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'File.php',
                          ),
                        ),
                        1 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'FileAttribsFilter.php',
                          ),
                        ),
                        2 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'FileContents.php',
                          ),
                        ),
                        3 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'FileContentsMulti.php',
                          ),
                        ),
                        4 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'FileInstallationFilter.php',
                          ),
                        ),
                        5 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'FileTag.php',
                          ),
                        ),
                        6 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'PackagingIterator.php',
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
                        'role' => 'php',
                        'name' => 'Exception.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'ValidatorInterface.php',
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'v2.php',
                      ),
                    ),
                  ),
                ),
                9 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Registry',
                  ),
                  'dir' => 
                  array (
                    'attribs' => 
                    array (
                      'name' => 'Sqlite',
                    ),
                    'dir' => 
                    array (
                      'attribs' => 
                      array (
                        'name' => 'Channel',
                      ),
                      'file' => 
                      array (
                        0 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Mirror.php',
                          ),
                        ),
                        1 => 
                        array (
                          'attribs' => 
                          array (
                            'role' => 'php',
                            'name' => 'Mirrors.php',
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
                          'role' => 'php',
                          'name' => 'Creator.php',
                        ),
                      ),
                      1 => 
                      array (
                        'attribs' => 
                        array (
                          'role' => 'php',
                          'name' => 'Package.php',
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
                        'role' => 'php',
                        'name' => 'Base.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Channel.php',
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Config.php',
                      ),
                    ),
                    3 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Exception.php',
                      ),
                    ),
                    4 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Package.php',
                      ),
                    ),
                    5 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Sqlite.php',
                      ),
                    ),
                    6 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Xml.php',
                      ),
                    ),
                  ),
                ),
                10 => 
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
                        'role' => 'php',
                        'name' => '10.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => '11.php',
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => '13.php',
                      ),
                    ),
                    3 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Exception.php',
                      ),
                    ),
                    4 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'HTTPException.php',
                      ),
                    ),
                  ),
                ),
                11 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'ScriptFrontend',
                  ),
                  'file' => 
                  array (
                    'attribs' => 
                    array (
                      'role' => 'php',
                      'name' => 'Commands.php',
                    ),
                  ),
                ),
                12 => 
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
                          'role' => 'php',
                          'name' => 'rw.php',
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
                          'role' => 'php',
                          'name' => 'rw.php',
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
                          'role' => 'php',
                          'name' => 'rw.php',
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
                          'role' => 'php',
                          'name' => 'rw.php',
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
                        'role' => 'php',
                        'name' => 'Common.php',
                      ),
                    ),
                    1 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Exception.php',
                      ),
                    ),
                    2 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Postinstallscript.php',
                      ),
                    ),
                    3 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Replace.php',
                      ),
                    ),
                    4 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Unixeol.php',
                      ),
                    ),
                    5 => 
                    array (
                      'attribs' => 
                      array (
                        'role' => 'php',
                        'name' => 'Windowseol.php',
                      ),
                    ),
                  ),
                ),
                13 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Validate',
                  ),
                  'file' => 
                  array (
                    'attribs' => 
                    array (
                      'role' => 'php',
                      'name' => 'Exception.php',
                    ),
                  ),
                ),
                14 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'Validator',
                  ),
                  'file' => 
                  array (
                    'attribs' => 
                    array (
                      'role' => 'php',
                      'name' => 'PECL.php',
                    ),
                  ),
                ),
                15 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'XMLParser',
                  ),
                  'file' => 
                  array (
                    'attribs' => 
                    array (
                      'role' => 'php',
                      'name' => 'Exception.php',
                    ),
                  ),
                ),
                16 => 
                array (
                  'attribs' => 
                  array (
                    'name' => 'XMLWriter',
                  ),
                  'file' => 
                  array (
                    'attribs' => 
                    array (
                      'role' => 'php',
                      'name' => 'Exception.php',
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
                    'role' => 'php',
                    'name' => 'Channel.php',
                  ),
                ),
                1 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'ChannelRegistry.php',
                  ),
                ),
                2 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'Config.php',
                  ),
                ),
                3 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'DirectedGraph.php',
                  ),
                ),
                4 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'Downloader.php',
                  ),
                ),
                5 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'Exception.php',
                  ),
                ),
                6 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'FileTransactions.php',
                  ),
                ),
                7 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'ChannelInterface.php',
                  ),
                ),
                8 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'ChannelRegistryInterface.php',
                  ),
                ),
                9 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'FileTransactionInterface.php',
                  ),
                ),
                10 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'Installer.php',
                  ),
                ),
                11 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'PackageInterface.php',
                  ),
                ),
                12 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'RegistryInterface.php',
                  ),
                ),
                13 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'Log.php',
                  ),
                ),
                14 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'OSGuess.php',
                  ),
                ),
                15 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'Package.php',
                  ),
                ),
                16 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'PackageFile.php',
                  ),
                ),
                17 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'Registry.php',
                  ),
                ),
                18 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'REST.php',
                  ),
                ),
                19 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'Validate.php',
                  ),
                ),
                20 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'XMLParser.php',
                  ),
                ),
                21 => 
                array (
                  'attribs' => 
                  array (
                    'role' => 'php',
                    'name' => 'XMLWriter.php',
                  ),
                ),
              ),
            ),
            'file' => 
            array (
              'attribs' => 
              array (
                'role' => 'php',
                'name' => 'Pyrus.php',
              ),
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
          'min' => '5.2.0',
        ),
        'pearinstaller' => 
        array (
          'min' => '2.0.0a1',
        ),
      ),
    ),
    'phprelease' => '',
  ),
), $ret->getThingy(), 'test');
?>
===DONE===
--EXPECT--
===DONE===