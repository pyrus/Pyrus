--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::build(), basic test (this actually builds an extension)
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) === 'WIN') {
    die('skip requires unix to work');
}
?>
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
$c->ext_dir = __DIR__ . '/testit/ext';
restore_include_path();
$c->saveConfig();

ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands(true);
$cli->run($args = array (__DIR__ . '/testit', 'install', __DIR__.'/build/docblock-0.2.0.tar'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Installed pecl.php.net/docblock-0.2.0' . "\n"
                    . " ==> To build this PECL package, use the build command\n",
                    $contents,
                    'installation');
$testpackage = new PEAR2_Pyrus_Package(__DIR__.'/build/docblock-0.2.0.tar');
$test->assertEquals('1.4.3', $testpackage->dependencies['required']->pearinstaller->min, 'tar pearinstaller dep');
$test->assertEquals('1.4.3', PEAR2_Pyrus_Config::current()->registry->package['pecl/docblock']
                               ->dependencies['required']->pearinstaller->min, 'same after installation');

ob_start();
$cli->run($args = array (__DIR__ . '/testit', 'build', 'pecl/docblock'));
$contents = ob_get_contents();
ob_end_clean();

$start = 'Using PEAR installation found at ' . __DIR__ . '/testit
Building PECL extensions
cleaning build directory ' . __DIR__ . '/testit/src/docblock
running: phpize --clean 2>&1
Cleaning..
building in ' . __DIR__ . '/testit/src/docblock
running: phpize 2>&1
Configuring for:';

$end = '@running: make INSTALL_ROOT="' . __DIR__ . '/testit/src/docblock/\.install" install 2>&1
Installing shared extensions:     ' . __DIR__ . '/testit/src/docblock/\.install' .
    PEAR2_Pyrus_Config::current()->defaultValue('ext_dir') . '/
\d+   4 drwxr\-xr\-x 3 user user       4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr
\d+   4 drwxr\-xr\-x 3 user user       4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local
\d+   4 drwxr\-xr\-x 3 user user       4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local/lib
\d+   4 drwxr\-xr\-x 3 user user       4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local/lib/php
\d+   4 drwxr\-xr\-x 3 user user       4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local/lib/php/extensions
\d+   4 drwxr\-xr\-x 2 user user       4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local/lib/php/extensions/[^/]+
\d+ 156 \-rwxr\-xr\-x 1 user user\s+\d+ \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local/lib/php/extensions/[^/]+/docblock\.so
Installing \'' . __DIR__ . '/testit/ext/docblock\.so\'
@';

$test->assertEquals($start, substr($contents, 0, strlen($start)), 'beginning of contents');
$test->assertRegex($end, substr($contents, strpos($contents, 'running: make INSTALL_ROOT')), 'end of contents');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===