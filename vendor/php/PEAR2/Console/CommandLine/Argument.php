<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PEAR2\Console\CommandLine package.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to the MIT license that is available
 * through the world-wide-web at the following URI:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Console
 * @package   PEAR2\Console\CommandLine
 * @author    David JEAN LOUIS <izimobil@gmail.com>
 * @copyright 2007-2009 David JEAN LOUIS
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @version   SVN: $Id$
 * @link      http://pear.php.net/package/Console_CommandLine
 * @since     File available since release 0.1.0
 * @filesource
 */

/**
 * Class that represent a command line argument.
 *
 * @category  Console
 * @package   PEAR2\Console\CommandLine
 * @author    David JEAN LOUIS <izimobil@gmail.com>
 * @copyright 2007-2009 David JEAN LOUIS
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Console_CommandLine
 * @since     Class available since release 0.1.0
 */
namespace PEAR2\Console\CommandLine;
class Argument extends Element
{
    // Public properties {{{

    /**
     * Setting this to true will tell the parser that the argument expects more
     * than one argument and that argument values should be stored in an array.
     *
     * @var boolean $multiple Whether the argument expects multiple values
     */
    public $multiple = false;

    /**
     * Setting this to true will tell the parser that the argument is optional
     * and can be ommited.
     * Note that it is not a good practice to make arguments optional, it is
     * the role of the options to be optional, by essence.
     *
     * @var boolean $optional Whether the argument is optional or not.
     */
    public $optional = false;

    // }}}
    // validate() {{{

    /**
     * Validates the argument instance.
     *
     * @return void
     * @throws PEAR2\Console\CommandLine_Exception
     * @todo use exceptions
     */
    public function validate()
    {
        // check if the argument name is valid
        if (!preg_match('/^[a-zA-Z_\x7f-\xff]+[a-zA-Z0-9_\x7f-\xff]*$/',
            $this->name)) {
            \PEAR2\Console\CommandLine::triggerError(
                'argument_bad_name',
                E_USER_ERROR,
                array('{$name}' => $this->name)
            );
        }
        if (!$this->optional && $this->default !== null) {
            Console_CommandLine::triggerError(
                'argument_no_default',
                E_USER_ERROR
            );
        }
        parent::validate();
    }

    // }}}
}
