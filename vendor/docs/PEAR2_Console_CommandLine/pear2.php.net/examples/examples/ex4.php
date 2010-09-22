<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PEAR PEAR2_Console_CommandLine package.
 *
 * A simple example demonstrating the use of subcommands.
 * (Same as ex3.php but using an xml file).
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to the MIT license that is available
 * through the world-wide-web at the following URI:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Console 
 * @package   PEAR2_Console_CommandLine
 * @author    David JEAN LOUIS <izimobil@gmail.com>
 * @copyright 2007-2009 David JEAN LOUIS
 * @license   http://opensource.org/licenses/mit-license.php MIT License 
 * @version   CVS: $Id: ex4.php,v 1.1 2008/12/06 11:46:28 izi Exp $
 * @link      http://pear.php.net/package/Console_CommandLine
 * @since     File available since release 0.1.0
 */

// uncomment this when package won't be in the SandBox anymore
// $basedir = __DIR__ . '/../..';
$basedir = __DIR__ . '/../../..';

// Include PEAR2 autoload
require_once $basedir . '/autoload.php';

// create the parser
$xmlfile = __DIR__ . DIRECTORY_SEPARATOR . 'ex4.xml';
$parser  = PEAR2_Console_CommandLine::fromXmlFile($xmlfile);

// run the parser
try {
    $result = $parser->parse();
    if ($result->command_name) {
        $st = $result->command->options['reverse'] 
            ? strrev($result->command->args['text'])
            : $result->command->args['text'];
        if ($result->command_name == 'foo') { 
            echo "Foo says: $st\n";
        } else if ($result->command_name == 'bar') {
            echo "Bar says: $st\n";
        }
    }
} catch (Exception $exc) {
    $parser->displayError($exc->getMessage());
}

?>
