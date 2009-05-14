<?php

if (!isset($_SERVER['argv'],$_SERVER['argv'][1])
    || $_SERVER['argv'][1] == '--help') {
    echo "This script requires at least one argument.\n";
    echo "ignorexdebug.php dirname\n";
    exit();
}

for ($i=1; $i<$_SERVER['argc']; $i++) {
    if (is_dir($_SERVER['argv'][$i])) {
        exec('svn propset svn:ignore *.xdebug '.$_SERVER['argv'][$i]);
    }
}