--TEST--
\Pyrus\ScriptFrontend\Commands::build(), basic test (this actually builds an extension)
--SKIPIF--
<?php
if (!is_writable(ini_get('extension_dir'))) {
    die('skip extension dir must be writable');
}
if (substr(PHP_OS, 0, 3) === 'WIN') {
    die('skip requires unix to work');
}
?>
--ENV--
return <<<END
PATH=/usr/local/bin:/usr/bin:/bin
END;
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$c = getTestConfig();

ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'install', __DIR__.'/build/docblock-0.2.0.tar'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . 'Installed pecl.php.net/docblock-0.2.0' . "\n"
                    . " ==> To build this PECL package, use the build command\n",
                    $contents,
                    'installation');
$testpackage = new \Pyrus\Package(__DIR__.'/build/docblock-0.2.0.tar');
$test->assertEquals('1.4.3', $testpackage->dependencies['required']->pearinstaller->min, 'tar pearinstaller dep');
$test->assertEquals('1.4.3', \Pyrus\Config::current()->registry->package['pecl/docblock']
                               ->dependencies['required']->pearinstaller->min, 'same after installation');

ob_start();
$cli->run($args = array (TESTDIR, 'build', 'pecl/docblock'));
$contents = ob_get_contents();
ob_end_clean();

$start = 'Using PEAR installation found at ' . TESTDIR . '
Building PECL extensions
cleaning build directory ' . TESTDIR . '/src/docblock
running: phpize --clean 2>&1
Cleaning..
building in ' . TESTDIR . '/src/docblock
running: phpize 2>&1
Configuring for:';

$end = '@running: make INSTALL_ROOT="' . TESTDIR . '/src/docblock/\.install" install 2>&1
Installing shared extensions:     ' . TESTDIR . '/src/docblock/\.install' .
    \Pyrus\Config::current()->defaultValue('ext_dir') . '/
\d+   \d drwxr\-xr\-x 3 \w+ \w+\s+4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr
\d+   \d drwxr\-xr\-x 3 \w+ \w+\s+4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local
\d+   \d drwxr\-xr\-x 3 \w+ \w+\s+4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local/lib
\d+   \d drwxr\-xr\-x 3 \w+ \w+\s+4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local/lib/php
\d+   \d drwxr\-xr\-x 3 \w+ \w+\s+4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local/lib/php/extensions
\d+   \d drwxr\-xr\-x 2 \w+ \w+\s+4096 \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local/lib/php/extensions/[^/]+
\d+ \d+ \-rwxr\-xr\-x 1 \w+ \w+\s+\d+ \d{4}\-\d{2}\-\d{2} \d{2}:\d{2} ' . __DIR__ .
    '/testit/src/docblock/\.install/usr/local/lib/php/extensions/[^/]+/docblock\.so
Installing \'' . TESTDIR . '/ext/docblock\.so\'
@';

$test->assertEquals($start, substr($contents, 0, strlen($start)), 'beginning of contents');
$test->assertRegex($end, substr($contents, strpos($contents, 'running: make INSTALL_ROOT')), 'end of contents');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
