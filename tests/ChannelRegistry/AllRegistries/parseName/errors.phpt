--TEST--
\Pyrus\ChannelRegistry\Base::parseName()
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$creg = new \Pyrus\ChannelRegistry(TESTDIR);
$testit = function($name, $message, $why) use ($test, $creg) {
    try {
        $creg->parseName($name);
        throw new Exception($name . ' should have failed and did not');
    } catch ( \Pyrus\ChannelRegistry\Exception $e) {
        $test->assertEquals('Unable to process package name', $e->getMessage(),
                            'exception message');
        $test->assertEquals('Pyrus\ChannelRegistry\ParseException',
                            get_class($e->getPrevious()), 'cause class ' . $name);
        $test->assertEquals($message, $e->getPrevious()->getMessage(),
                            'message ' . $name);
        $test->assertEquals($why, $e->getPrevious()->why, 'why '. $name);
    }
};
$testit('ftp://oops', 'parsePackageName(): only channel:// uris may ' .
                        'be downloaded, not "ftp://oops"', 'scheme');
$testit('https://', 'parsePackageName(): array $param ' .
                        'must contain a valid package name in "https://"', 'path');
$testit('/oops', 'parsePackageName(): this is not ' .
                        'a package name, it begins with "/" in "/oops"', 'invalid');
$testit('oops-too-many', 'parseName(): only one version/state ' .
                        'delimiter "-" is allowed in "oops-too-many"', 'invalid');
$testit('pear2/^$@', 'parseName(): invalid package name "' .
                        '^$@" in "pear2/^$@"', 'package');
$testit('pear2/foo#$%', 'parseName(): dependency group "' .
                        '$%" is not a valid group name in "pear2/foo#$%"', 'group');
$testit('pear2/foo-*', 'parseName(): "*' .
                        '" is neither a valid version nor a valid state in "' .
                        'pear2/foo-*"', 'version/state');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===