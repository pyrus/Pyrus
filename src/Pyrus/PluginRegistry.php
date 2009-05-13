<?php
/**
 * PEAR2_Pyrus_PluginRegistry
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
 * Registry manager for Pyrus plugins
 *
 * The plugin manager is a standard Pyrus registry, but also has
 * specialized commands to retrieve command plugins, custom roles/tasks, and scripts
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PluginRegistry extends PEAR2_Pyrus_Registry
{
    function retrieveCommands()
    {
    }

    function retrieveRoles()
    {
        
    }

    function retrieveTasks()
    {
    }

    function retrieveScripts()
    {
        
    }

    function getRole($role)
    {
    }

    function getTask($role)
    {
    }
}
