<?php
if (!class_exists('Phar')) {
    if (!class_exists('PHP_Archive')) {
@PHPARCHIVE@
    }
    if (!in_array('phar', stream_get_wrappers(), true)) {
        stream_wrapper_register('phar', 'PHP_Archive');
    }
}
define('PYRUS_PHAR_FILE', __FILE__);
include 'phar://' . __FILE__ . '/__index.php';
__HALT_COMPILER();