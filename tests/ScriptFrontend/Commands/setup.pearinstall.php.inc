<?php
// first, install the package using PEAR
$temp_path = __DIR__ . '/testit';
mkdir($temp_path);
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'php')) {
    mkdir($php_dir = $temp_path . DIRECTORY_SEPARATOR . 'php');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'data')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'data');
}
if (!is_dir($cfg_dir = $temp_path . DIRECTORY_SEPARATOR . 'cfg')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'cfg');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'doc')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'doc');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'test')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'test');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'ext')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'ext');
    $ext_dir = $temp_path . DIRECTORY_SEPARATOR . 'ext';
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'script')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'script');
}
if (!is_dir($tmp_dir = $temp_path . DIRECTORY_SEPARATOR . 'tmp')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'tmp');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'bin')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'bin');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'cache')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'cache');
}
// make the fake configuration - we'll use one of these and it should work
$config = serialize(array(
    'preferred_state' => 'stable',
    'cache_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cache',
    'php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'php',
    'cfg_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cfg',
    'ext_dir' => $temp_path . DIRECTORY_SEPARATOR . 'ext',
    'data_dir' => $temp_path . DIRECTORY_SEPARATOR . 'data',
    'www_dir' => $temp_path . DIRECTORY_SEPARATOR . 'www',
    'doc_dir' => $temp_path . DIRECTORY_SEPARATOR . 'doc',
    'test_dir' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'download_dir' => $temp_path . DIRECTORY_SEPARATOR . 'tmp',
    'temp_dir' => $temp_path . DIRECTORY_SEPARATOR . 'tmp',
    'bin_dir' => $temp_path . DIRECTORY_SEPARATOR . 'bin',));
touch($temp_path . DIRECTORY_SEPARATOR . 'pear.conf');
$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'pear.conf', 'w');
fwrite($fp, $config);
fclose($fp);
touch($temp_path . DIRECTORY_SEPARATOR . 'pear.ini');
$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'pear.ini', 'w');
fwrite($fp, $config);
fclose($fp);

putenv('PHP_PEAR_SYSCONF_DIR='.$temp_path);
$home = getenv('HOME');
if (!empty($home)) {
    // for PEAR_Config initialization
    putenv('HOME="'.$temp_path);
}
require_once "PEAR/Installer.php";
require_once 'PEAR/Frontend/CLI.php';
$config = &PEAR_Config::singleton($temp_path . '/pear.ini');

// now install the package
$pathtopackagexml = dirname(__FILE__) . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'Net_URL-1.0.15.tar';
$ui = new PEAR_Frontend_CLI;
$dp = new PEAR_Downloader($ui, array('offline' => true), $config);
$result = $dp->download(array($pathtopackagexml));
$installer = new PEAR_Installer($ui);

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$ret = $installer->install($result[0], $dp->getOptions());
unset($ret['dirtree']);
$test->assertEquals(array (
  'attribs' =>
  array (
    'packagerversion' => '1.6.0',
    'version' => '2.0',
    'xmlns' => 'http://pear.php.net/dtd/package-2.0',
    'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd',
  ),
  'name' => 'Net_URL',
  'channel' => 'pear.php.net',
  'summary' => 'Easy parsing of Urls',
  'description' => 'Provides easy parsing of URLs and their constituent parts.',
  'lead' =>
  array (
    0 =>
    array (
      'name' => 'Richard heyes',
      'user' => 'richard',
      'email' => 'richard@php.net',
      'active' => 'no',
    ),
    1 =>
    array (
      'name' => 'David Coallier',
      'user' => 'davidc',
      'email' => 'davidc@php.net',
      'active' => 'yes',
    ),
  ),
  'date' => $ret['date'],
  'time' => $ret['time'],
  'version' =>
  array (
    'release' => '1.0.15',
    'api' => '1.0.15',
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
      'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
    ),
    '_content' => 'BSD',
  ),
  'notes' => '- Fixed Bug #11385 $url was defined.
- Fixed Doc #11017 Fixed docblock problem (cweiske) 
- Fixed Bug #11008 (Override object properties in initialize())
- Fixed Bug #6338 Wrong query string handling
- Fixed Bug #704  Fixed hardcoded value and use ini_get(arg_separator)
- Fixed Bug #1036 Improper default port number detection
- Fixed Bug #1447 use $_SERVER instead of HTTP_SERVER_VARS
- Fixed Bug #1558 _parseRawQueryString failed if arg_sep.input was more than 1 char.
- Fixed Bug #1682 File was in DOC format rather than Unix
- Fixed Bug #2334 Sqare brackets in var names were encoded
- Fixed Bug #2824 Better error handling support.
- Fixed bug #3159 setProtocol was calling getStandardPort incorrectly',
  'contents' =>
  array (
    'dir' =>
    array (
      'attribs' =>
      array (
        'name' => '/',
      ),
      'file' =>
      array (
        0 =>
        array (
          'attribs' =>
          array (
            'baseinstalldir' => 'Net',
            'md5sum' => 'af793351a5f00e31a2df697b54cfbc02',
            'name' => 'docs/example.php',
            'role' => 'doc',
          ),
        ),
        1 =>
        array (
          'attribs' =>
          array (
            'baseinstalldir' => 'Net',
            'md5sum' => '0488b5531c31332113100971be7ba2d9',
            'name' => 'docs/6470.php',
            'role' => 'doc',
          ),
        ),
        2 =>
        array (
          'attribs' =>
          array (
            'baseinstalldir' => 'Net',
            'md5sum' => 'c7e690d656b56cc48a12399331a35b27',
            'name' => 'URL.php',
            'role' => 'php',
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
        'min' => '4.0.0',
      ),
      'pearinstaller' =>
      array (
        'min' => '1.4.0b1',
      ),
    ),
  ),
  'phprelease' => '',
  'changelog' =>
  array (
    'release' =>
    array (
      0 =>
      array (
        'version' =>
        array (
          'release' => '1.0.14',
          'api' => '1.0.14',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2004-06-19',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Whitespace',
      ),
      1 =>
      array (
        'version' =>
        array (
          'release' => '1.0.13',
          'api' => '1.0.13',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2004-06-05',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Fix bug 1558',
      ),
      2 =>
      array (
        'version' =>
        array (
          'release' => '1.0.12',
          'api' => '1.0.12',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2004-05-08',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Bug fixes release (#704 and #1036)',
      ),
      3 =>
      array (
        'version' =>
        array (
          'release' => '1.0.11',
          'api' => '1.0.11',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2004-01-17',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Bug fixes release (#83 and #471)',
      ),
      4 =>
      array (
        'version' =>
        array (
          'release' => '1.0.10',
          'api' => '1.0.10',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-04-06',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Be more flexible in what constitutes a scheme',
      ),
      5 =>
      array (
        'version' =>
        array (
          'release' => '1.0.9',
          'api' => '1.0.9',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-04-05',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Fix couple of absolute URL bugs.',
      ),
      6 =>
      array (
        'version' =>
        array (
          'release' => '1.0.8',
          'api' => '1.0.8',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-03-06',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Various bugs. Remove auto setting of default url to \'/\' if a url is supplied
to the constructor. May cause BC issues.',
      ),
      7 =>
      array (
        'version' =>
        array (
          'release' => '1.0.7',
          'api' => '1.0.7',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-12-07',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Added method to resolve URL paths of //, ../ and ./',
      ),
      8 =>
      array (
        'version' =>
        array (
          'release' => '1.0.6',
          'api' => '1.0.6',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-12-07',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Make usage of [] optional',
      ),
      9 =>
      array (
        'version' =>
        array (
          'release' => '1.0.5',
          'api' => '1.0.5',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-11-14',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Allow for URLS such as ...?foo',
      ),
      10 =>
      array (
        'version' =>
        array (
          'release' => '1.0.4',
          'api' => '1.0.4',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-07-27',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'License change',
      ),
      11 =>
      array (
        'version' =>
        array (
          'release' => '1.0.3',
          'api' => '1.0.3',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-06-20',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Now uses HTTP_HOST if available.',
      ),
      12 =>
      array (
        'version' =>
        array (
          'release' => '1.0.2',
          'api' => '1.0.2',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-04-28',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'updated to fix a minor irritation when running on windows',
      ),
      13 =>
      array (
        'version' =>
        array (
          'release' => '1.0.1',
          'api' => '1.0.1',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-04-28',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'Maintenance release. Bugs fixed with path detection and defaults.',
      ),
      14 =>
      array (
        'version' =>
        array (
          'release' => '1.0',
          'api' => '1.0',
        ),
        'stability' =>
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'date' => '2002-02-17',
        'license' =>
        array (
          'attribs' =>
          array (
            'uri' => 'http://www.opensource.org/licenses/bsd-license.php',
          ),
          '_content' => 'BSD',
        ),
        'notes' => 'This is the initial release of the Net_URL package.',
      ),
    ),
  ),
  'filelist' =>
  array (
    'docs/example.php' =>
    array (
      'baseinstalldir' => 'Net',
      'md5sum' => 'af793351a5f00e31a2df697b54cfbc02',
      'name' => 'docs/example.php',
      'role' => 'doc',
      'installed_as' => __DIR__ . '/testit/doc/Net_URL/docs/example.php',
    ),
    'docs/6470.php' =>
    array (
      'baseinstalldir' => 'Net',
      'md5sum' => '0488b5531c31332113100971be7ba2d9',
      'name' => 'docs/6470.php',
      'role' => 'doc',
      'installed_as' => __DIR__ . '/testit/doc/Net_URL/docs/6470.php',
    ),
    'URL.php' =>
    array (
      'baseinstalldir' => 'Net',
      'md5sum' => 'c7e690d656b56cc48a12399331a35b27',
      'name' => 'URL.php',
      'role' => 'php',
      'installed_as' => __DIR__ . '/testit/php/Net/URL.php',
    ),
  ),
  '_lastversion' => NULL,
  'old' =>
  array (
    'version' => '1.0.15',
    'release_date' => '2007-06-28',
    'release_state' => 'stable',
    'release_license' => 'BSD',
    'release_notes' => '- Fixed Bug #11385 $url was defined.
- Fixed Doc #11017 Fixed docblock problem (cweiske) 
- Fixed Bug #11008 (Override object properties in initialize())
- Fixed Bug #6338 Wrong query string handling
- Fixed Bug #704  Fixed hardcoded value and use ini_get(arg_separator)
- Fixed Bug #1036 Improper default port number detection
- Fixed Bug #1447 use $_SERVER instead of HTTP_SERVER_VARS
- Fixed Bug #1558 _parseRawQueryString failed if arg_sep.input was more than 1 char.
- Fixed Bug #1682 File was in DOC format rather than Unix
- Fixed Bug #2334 Sqare brackets in var names were encoded
- Fixed Bug #2824 Better error handling support.
- Fixed bug #3159 setProtocol was calling getStandardPort incorrectly',
    'release_deps' =>
    array (
      0 =>
      array (
        'type' => 'php',
        'rel' => 'ge',
        'version' => '4.0.0',
        'optional' => 'no',
      ),
      1 =>
      array (
        'type' => 'pkg',
        'channel' => 'pear.php.net',
        'name' => 'PEAR',
        'rel' => 'ge',
        'version' => '1.4.0b1',
        'optional' => 'no',
      ),
    ),
    'maintainers' =>
    array (
      0 =>
      array (
        'name' => 'Richard heyes',
        'email' => 'richard@php.net',
        'active' => 'no',
        'handle' => 'richard',
        'role' => 'lead',
      ),
      1 =>
      array (
        'name' => 'David Coallier',
        'email' => 'davidc@php.net',
        'active' => 'yes',
        'handle' => 'davidc',
        'role' => 'lead',
      ),
    ),
  ),
  'xsdversion' => '2.0',
), $ret, 'PEAR setup failed');
