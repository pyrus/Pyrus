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
require dirname(__DIR__) . '/setup.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
$c->bin_dir = __DIR__ . '/testit/bin';
$c->ext_dir = __DIR__ . '/testit/ext';
restore_include_path();
$c->saveConfig();

ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
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

$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . 'Building PECL extensions
cleaning build directory /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock
running: phpize --clean 2>&1
Cleaning..
building in /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock
running: phpize 2>&1
Configuring for:
PHP Api Version:         20041225
Zend Module Api No:      20090115
Zend Extension Api No:   220090115

Warning: Invalid argument supplied for foreach() in /home/user/workspace/all/Pyrus/src/Pyrus/PECLBuild.php on line 276
building in /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock
running: /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/configure 2>&1
creating cache ./config.cache
checking for Cygwin environment... no
checking for mingw32 environment... no
checking for egrep... grep -E
checking for a sed that does not truncate output... /bin/sed
checking for gcc... gcc
checking whether the C compiler (gcc  ) works... yes
checking whether the C compiler (gcc  ) is a cross-compiler... no
checking whether we are using GNU C... yes
checking whether gcc accepts -g... yes
checking how to run the C preprocessor... gcc -E
checking for icc... no
checking for suncc... no
checking whether gcc and cc understand -c and -o together... yes
checking for system library directory... lib
checking if compiler supports -R... no
checking if compiler supports -Wl,-rpath,... yes
checking host system type... i686-pc-linux-gnu
checking target system type... i686-pc-linux-gnu
checking for PHP prefix... /usr/local
checking for PHP includes... -I/usr/local/include/php -I/usr/local/include/php/main -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -I/usr/local/include/php/ext -I/usr/local/include/php/ext/date/lib
checking for PHP extension directory... /usr/local/lib/php/extensions/debug-non-zts-20090115
checking for PHP installed headers prefix... /usr/local/include/php
checking if debug is enabled... yes
checking if zts is enabled... yes
checking for re2c... re2c
checking for re2c version... 0.13.5 (ok)
checking for gawk... no
checking for nawk... nawk
checking if nawk is broken... no
checking whether to enable docblock tokenizer support... yes, shared
checking build system type... i686-pc-linux-gnu
checking for ld used by gcc... /usr/bin/ld
checking if the linker (/usr/bin/ld) is GNU ld... yes
checking for /usr/bin/ld option to reload object files... -r
checking for BSD-compatible nm... /usr/bin/nm -B
checking whether ln -s works... yes
checking how to recognise dependent libraries... pass_all
checking for object suffix... o
checking for executable suffix... no
checking for dlfcn.h... yes
checking the maximum length of command line arguments... 32768
checking command to parse /usr/bin/nm -B output from gcc object... ok
checking for objdir... .libs
checking for ar... ar
checking for ranlib... ranlib
checking for strip... strip
checking if gcc static flag  works... yes
checking if gcc supports -fno-rtti -fno-exceptions... no
checking for gcc option to produce PIC... -fPIC
checking if gcc PIC flag -fPIC works... yes
checking if gcc supports -c -o file.o... yes
checking whether the gcc linker (/usr/bin/ld) supports shared libraries... yes
checking whether -lc should be explicitly linked in... no
checking dynamic linker characteristics... GNU/Linux ld.so
checking how to hardcode library paths into programs... immediate
checking whether stripping libraries is possible... yes
checking if libtool supports shared libraries... yes
checking whether to build shared libraries... yes
checking whether to build static libraries... no

creating libtool
appending configuration tag "CXX" to libtool
updating cache ./config.cache
creating ./config.status
creating config.h
running: make 2>&1
/bin/sh /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/libtool --mode=compile gcc  -I. -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock -DPHP_ATOM_INC -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/include -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/main -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock -I/usr/local/include/php -I/usr/local/include/php/main -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -I/usr/local/include/php/ext -I/usr/local/include/php/ext/date/lib  -I/usr/local/include/php -DHAVE_CONFIG_H  -g -O0   -c /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/docblock.c -o docblock.lo
mkdir .libs
 gcc -I. -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock -DPHP_ATOM_INC -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/include -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/main -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock -I/usr/local/include/php -I/usr/local/include/php/main -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -I/usr/local/include/php/ext -I/usr/local/include/php/ext/date/lib -I/usr/local/include/php -DHAVE_CONFIG_H -g -O0 -c /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/docblock.c  -fPIC -DPIC -o .libs/docblock.o
/bin/sh /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/libtool --mode=compile gcc  -I. -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock -DPHP_ATOM_INC -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/include -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/main -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock -I/usr/local/include/php -I/usr/local/include/php/main -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -I/usr/local/include/php/ext -I/usr/local/include/php/ext/date/lib  -I/usr/local/include/php -DHAVE_CONFIG_H  -g -O0   -c /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/docblock_scan.c -o docblock_scan.lo
 gcc -I. -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock -DPHP_ATOM_INC -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/include -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/main -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock -I/usr/local/include/php -I/usr/local/include/php/main -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -I/usr/local/include/php/ext -I/usr/local/include/php/ext/date/lib -I/usr/local/include/php -DHAVE_CONFIG_H -g -O0 -c /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/docblock_scan.c  -fPIC -DPIC -o .libs/docblock_scan.o
/bin/sh /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/libtool --mode=link gcc -DPHP_ATOM_INC -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/include -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/main -I/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock -I/usr/local/include/php -I/usr/local/include/php/main -I/usr/local/include/php/TSRM -I/usr/local/include/php/Zend -I/usr/local/include/php/ext -I/usr/local/include/php/ext/date/lib  -I/usr/local/include/php -DHAVE_CONFIG_H  -g -O0   -o docblock.la -export-dynamic -avoid-version -prefer-pic -module -rpath /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/modules  docblock.lo docblock_scan.lo
gcc -shared  .libs/docblock.o .libs/docblock_scan.o   -Wl,-soname -Wl,docblock.so -o .libs/docblock.so
creating docblock.la
(cd .libs && rm -f docblock.la && ln -s ../docblock.la docblock.la)
/bin/sh /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/libtool --mode=install cp ./docblock.la /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/modules
cp ./.libs/docblock.so /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/modules/docblock.so
cp ./.libs/docblock.lai /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/modules/docblock.la
PATH="$PATH:/sbin" ldconfig -n /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/modules
----------------------------------------------------------------------
Libraries have been installed in:
   /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/modules

If you ever happen to want to link against installed libraries
in a given directory, LIBDIR, you must either use libtool, and
specify the full pathname of the library, or use the `-LLIBDIR\'
flag during linking and do at least one of the following:
   - add LIBDIR to the `LD_LIBRARY_PATH\' environment variable
     during execution
   - add LIBDIR to the `LD_RUN_PATH\' environment variable
     during linking
   - use the `-Wl,--rpath -Wl,LIBDIR\' linker flag
   - have your system administrator add LIBDIR to `/etc/ld.so.conf\'

See any operating system documentation about shared libraries for
more information, such as the ld(1) and ld.so(8) manual pages.
----------------------------------------------------------------------

Build complete.
Don\'t forget to run \'make test\'.

running: make INSTALL_ROOT="/home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/.install" install 2>&1
Installing shared extensions:     /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/.install/usr/local/lib/php/extensions/debug-non-zts-20090115/
485440   4 drwxr-xr-x 3 user user       4096 2009-05-29 22:41 /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/.install/usr
485441   4 drwxr-xr-x 3 user user       4096 2009-05-29 22:41 /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/.install/usr/local
485442   4 drwxr-xr-x 3 user user       4096 2009-05-29 22:41 /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/.install/usr/local/lib
485443   4 drwxr-xr-x 3 user user       4096 2009-05-29 22:41 /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/.install/usr/local/lib/php
485444   4 drwxr-xr-x 3 user user       4096 2009-05-29 22:41 /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/.install/usr/local/lib/php/extensions
485445   4 drwxr-xr-x 2 user user       4096 2009-05-29 22:41 /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/.install/usr/local/lib/php/extensions/debug-non-zts-20090115
485439 156 -rwxr-xr-x 1 user user     152029 2009-05-29 22:41 /home/user/workspace/all/Pyrus/tests/ScriptFrontend/Commands/testit/src/docblock/.install/usr/local/lib/php/extensions/debug-non-zts-20090115/docblock.so
',
                    $contents,
                    'build');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===