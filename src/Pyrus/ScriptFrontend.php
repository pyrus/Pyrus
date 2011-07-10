<?php
/**
 * Extension to PEAR2_Console_CommandLine
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * This class serves one purpose, removing the exit() call from displayUsage()
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace Pyrus;
class ScriptFrontend extends \PEAR2\Console\CommandLine
{
    public function addCommand($name, $params = array(), $overrideOK = false)
    {
        if ($name instanceof \PEAR2\Console\CommandLine\Command) {
            $testname = $name->name;
            $command = $name;
        } else {
            $params['name'] = $name;
            $command        = new ScriptFrontend\Command($params);
            $testname = $name;
        }

        if (isset($this->commands[$testname])) {
            if (!$overrideOK) {
                throw new ScriptFrontend\Exception('Cannot override existing command ' . $testname);
            }
        }

        $command->parent                = $this;
        $this->commands[$command->name] = $command;
        return $command;
    }

    /**
     * Display the usage help message to the user, but don't exit
     *
     * @param int $exitCode the exit code number
     *
     * @return void
     * @access public
     */
    public function displayUsage($exitCode = 1)
    {
        echo "\n", $this->renderer->usage();
    }
}