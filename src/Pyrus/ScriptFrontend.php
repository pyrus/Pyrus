<?php
/**
 * Extension to PEAR2_Console_CommandLine
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * This class serves one purpose, removing the exit() call from displayUsage()
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus;
class ScriptFrontend extends \PEAR2_Console_CommandLine
{
    public function addCommand($name, $params = array(), $overrideOK = false)
    {
        if ($name instanceof \PEAR2_Console_CommandLine_Command) {
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