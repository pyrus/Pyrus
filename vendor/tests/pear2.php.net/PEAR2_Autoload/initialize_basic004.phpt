--TEST--
Test PEAR2\Autoload initalization w/2nd path, loading class & writing map.
--FILE--
<?php
require __DIR__ . '/../src/PEAR2/Autoload.php';
PEAR2\Autoload::initialize(__DIR__.'/_files', __DIR__.'/_files/_files_map.php.inc');
echo testDir1\Foo::sayHello();

$map = include __DIR__.'/_files/_files_map.php.inc';
if (isset($map['testDir1\\Foo'])) {
    echo "class mapped\n";
} else {
    echo "class not mapped\n";
}

?>
--EXPECT--
class testDir1\Foo says hi
class mapped

--CLEAN--
<?php
// comment this out if you want to review the generated file map!
unlink(__DIR__.'/_files/_files_map.php.inc');
?>