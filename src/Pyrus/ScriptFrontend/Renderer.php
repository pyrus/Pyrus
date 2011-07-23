<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PEAR2_Console_CommandLine package.
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
 * @copyright 2007 David JEAN LOUIS
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @link      http://pear.php.net/package/Console_CommandLine
 * @since     File available since release 0.1.0
 */

/**
 * PEAR2_Console_CommandLine default renderer.
 *
 * @category  Console
 * @package   PEAR2_Console_CommandLine
 * @author    David JEAN LOUIS <izimobil@gmail.com>
 * @copyright 2007 David JEAN LOUIS
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Console_CommandLine
 * @since     Class available since release 0.1.0
 */
namespace Pyrus\ScriptFrontend;
class Renderer extends \PEAR2\Console\CommandLine\Renderer_Default
{
    /**
     * Return the command line usage message
     *
     * @return string the usage message
     * @access protected
     */
    protected function usageLine()
    {
        $usage = $this->parser->message_provider->get('USAGE_WORD') . ":\n";
        $ret   = $usage . '  ' . $this->name();
        if (count($this->parser->options) > 0) {
            $ret .= ' [/path/to/pyrus] ['
                . strtolower($this->parser->message_provider->get('OPTION_WORD'))
                . ']';
        }
        if (count($this->parser->args) > 0) {
            foreach ($this->parser->args as $name=>$arg) {
                $ret .= ' <' . $arg->help_name . ($arg->multiple?'...':'') . '>';
            }
        }
        return $this->columnWrap($ret, 2);
    }

    /**
     * Return the command line usage message for subcommands
     *
     * @return string
     * @access protected
     */
    protected function commandUsageLine()
    {
        if (count($this->parser->commands) == 0) {
            return '';
        }
        $ret = '  ' . $this->name();
        if (count($this->parser->options) > 0) {
            $ret .= ' [/path/to/pyrus] ['
                . strtolower($this->parser->message_provider->get('OPTION_WORD'))
                . ']';
        }
        //XXX
        $ret .= " <command> [options] [args]";
        return $this->columnWrap($ret, 2);
    }

    /**
     * Render the command list that will be displayed to the user, you can
     * override this method if you want to change the look of the list.
     *
     * @return string The formatted subcommand list
     */
    protected function commandList()
    {

        $commands = array();
        $col      = 0;
        foreach ($this->parser->commands as $cmdname=>$command) {
            $cmdname    = '  ' . $cmdname;
            $commands[] = array($cmdname, $command->description, $command->aliases);
            $ln         = strlen($cmdname);
            if ($col < $ln) {
                $col = $ln;
            }
        }

        $ret = $this->parser->message_provider->get('COMMAND_WORD') . ":";
        foreach ($commands as $command) {
            $text = str_pad($command[0], $col) . '  ' . $command[1];
            $ret .= "\n" . $this->columnWrap($text, $col+2);
        }

        return $ret;
    }

    /**
     * Wraps the text passed to the method at the specified width.
     *
     * @param string $text The text to wrap
     * @param int    $cw   The wrap width
     *
     * @return string The wrapped text
     */
    protected function columnWrap($text, $cw)
    {
        $tokens = explode("\n", $this->wrap($text));
        $ret    = $tokens[0];
        $text   = trim(substr($text, strlen($ret)));
        if (empty($text)) {
            return $ret;
        }

        $chunks = $this->wrap($text, $this->line_width - $cw);
        $tokens = explode("\n", $chunks);
        foreach ($tokens as $token) {
            if (!empty($token)) {
                $ret .= "\n" . str_repeat(' ', $cw) . $token;
            } else {
                $ret .= "\n";
            }
        }
        return $ret;
    }
}