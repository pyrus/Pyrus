<?php
/**
 * This file generates the pyrus.phar file and PEAR2 package for Pyrus.
 */
$rp = __DIR__ . '/../HTTP_Request/src/HTTP';
$cc = __DIR__ . '/../sandbox/Console_CommandLine/src/Console';
$extrafiles = array(
    'php/PEAR2/HTTP/Request.php' => $rp . '/Request.php',
    'php/PEAR2/HTTP/Request/Adapter.php' => $rp . '/Request/Adapter.php',
    'php/PEAR2/HTTP/Request/Adapter/Curl.php' => $rp . '/Request/Adapter/Curl.php',
    'php/PEAR2/HTTP/Request/Adapter/Http.php' => $rp . '/Request/Adapter/Http.php',
    'php/PEAR2/HTTP/Request/Adapter/Phpsocket.php' => $rp . '/Request/Adapter/Phpsocket.php',
    'php/PEAR2/HTTP/Request/Adapter/Phpstream.php' => $rp . '/Request/Adapter/Phpstream.php',
    'php/PEAR2/HTTP/Request/Exception.php' => $rp . '/Request/Exception.php',
    'php/PEAR2/HTTP/Request/Headers.php' => $rp . '/Request/Headers.php',
    'php/PEAR2/HTTP/Request/Listener.php' => $rp . '/Request/Listener.php',
    'php/PEAR2/HTTP/Request/Response.php' => $rp . '/Request/Response.php',
    'php/PEAR2/HTTP/Request/Uri.php' => $rp . '/Request/Uri.php',

    'php/PEAR2/Console/CommandLine.php' => $cc . '/CommandLine.php',
    'php/PEAR2/Console/CommandLine/Result.php' => $cc . '/CommandLine/Result.php',
    'php/PEAR2/Console/CommandLine/Renderer.php' => $cc . '/CommandLine/Renderer.php',
    'php/PEAR2/Console/CommandLine/Outputter.php' => $cc . '/CommandLine/Outputter.php',
    'php/PEAR2/Console/CommandLine/Option.php' => $cc . '/CommandLine/Option.php',
    'php/PEAR2/Console/CommandLine/MessageProvider.php' => $cc . '/CommandLine/MessageProvider.php',
    'php/PEAR2/Console/CommandLine/Exception.php' => $cc . '/CommandLine/Exception.php',
    'php/PEAR2/Console/CommandLine/Element.php' => $cc . '/CommandLine/Element.php',
    'php/PEAR2/Console/CommandLine/Command.php' => $cc . '/CommandLine/Command.php',
    'php/PEAR2/Console/CommandLine/Argument.php' => $cc . '/CommandLine/Argument.php',
    'php/PEAR2/Console/CommandLine/Action.php' => $cc . '/CommandLine/Action.php',
    'php/PEAR2/Console/CommandLine/Renderer/Default.php' => $cc . '/CommandLine/Renderer/Default.php',
    'php/PEAR2/Console/CommandLine/Outputter/Default.php' => $cc . '/CommandLine/Outputter/Default.php',
    'php/PEAR2/Console/CommandLine/MessageProvider/Default.php' => $cc . '/CommandLine/MessageProvider/Default.php',
    'php/PEAR2/Console/CommandLine/Action/Callback.php' => $cc . '/CommandLine/Action/Callback.php',
    'php/PEAR2/Console/CommandLine/Action/Counter.php' => $cc . '/CommandLine/Action/Counter.php',
    'php/PEAR2/Console/CommandLine/Action/Help.php' => $cc . '/CommandLine/Action/Help.php',
    'php/PEAR2/Console/CommandLine/Action/StoreFloat.php' => $cc . '/CommandLine/Action/StoreFloat.php',
    'php/PEAR2/Console/CommandLine/Action/StoreInt.php' => $cc . '/CommandLine/Action/StoreInt.php',
    'php/PEAR2/Console/CommandLine/Action/StoreString.php' => $cc . '/CommandLine/Action/StoreString.php',
    'php/PEAR2/Console/CommandLine/Action/StoreTrue.php' => $cc . '/CommandLine/Action/StoreTrue.php',
    'php/PEAR2/Console/CommandLine/Action/Version.php' => $cc . '/CommandLine/Action/Version.php',
);
