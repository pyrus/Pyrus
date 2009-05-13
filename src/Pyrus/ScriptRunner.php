<?php
/**
 * PEAR2_Pyrus_ScriptRunner
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
 * Post-install script runner for Pyrus
 *
 * This class handles the logic of navigating a post-install script's XML,
 * determining what questions to ask, and then passes the information to the
 * actual class.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_ScriptRunner
{
    protected $frontend;

    function __construct($frontend)
    {
        $this->frontend = $frontend;
    }

    function run(PEAR2_Pyrus_IPackageFile $package)
    {
        foreach ($package->scriptfiles as $file) {
            
        }
    }
    /**
     * Instruct the runInstallScript method to skip a paramgroup that matches the
     * id value passed in.
     *
     * This method is useful for dynamically configuring which sections of a post-install script
     * will be run based on the user's setup, which is very useful for making flexible
     * post-install scripts without losing the cross-Frontend ability to retrieve user input
     * @param string
     */
    function skipParamgroup($id)
    {
        $this->_skipSections[$id] = true;
    }

    function runPostinstallScripts($scripts)
    {
        foreach ($scripts as $i => $script) {
            $this->runInstallScript($scripts[$i]->_params, $scripts[$i]->_obj);
        }
    }

    /**
     * @param array $xml contents of postinstallscript tag
     * @param object $script post-installation script
     * @param string install|upgrade
     */
    function runInstallScript($xml, $script)
    {
        $this->_skipSections = array();
        if (!is_array($xml) || !isset($xml['paramgroup'])) {
            $script->run(array(), '_default');
            return;
        }

        $completedPhases = array();
        if (!isset($xml['paramgroup'][0])) {
            $xml['paramgroup'] = array($xml['paramgroup']);
        }

        foreach ($xml['paramgroup'] as $group) {
            if (isset($this->_skipSections[$group['id']])) {
                // the post-install script chose to skip this section dynamically
                continue;
            }

            if (isset($group['name'])) {
                $paramname = explode('::', $group['name']);
                if ($lastgroup['id'] != $paramname[0]) {
                    continue;
                }

                $group['name'] = $paramname[1];
                if (!isset($answers)) {
                    return;
                }

                if (isset($answers[$group['name']])) {
                    switch ($group['conditiontype']) {
                        case '=' :
                            if ($answers[$group['name']] != $group['value']) {
                                continue 2;
                            }
                        break;
                        case '!=' :
                            if ($answers[$group['name']] == $group['value']) {
                                continue 2;
                            }
                        break;
                        case 'preg_match' :
                            if (!@preg_match('/' . $group['value'] . '/',
                                  $answers[$group['name']])) {
                                continue 2;
                            }
                        break;
                        default :
                        return;
                    }
                }
            }

            $lastgroup = $group;
            if (isset($group['instructions'])) {
                $this->_display($group['instructions']);
            }

            if (!isset($group['param'][0])) {
                $group['param'] = array($group['param']);
            }

            if (isset($group['param'])) {
                if (method_exists($script, 'postProcessPrompts')) {
                    $prompts = $script->postProcessPrompts($group['param'], $group['id']);
                    if (!is_array($prompts) || count($prompts) != count($group['param'])) {
                        $this->outputData('postinstall', 'Error: post-install script did not ' .
                            'return proper post-processed prompts');
                        $prompts = $group['param'];
                    } else {
                        foreach ($prompts as $i => $var) {
                            if (!is_array($var) || !isset($var['prompt']) ||
                                  !isset($var['name']) ||
                                  ($var['name'] != $group['param'][$i]['name']) ||
                                  ($var['type'] != $group['param'][$i]['type'])
                            ) {
                                $this->outputData('postinstall', 'Error: post-install script ' .
                                    'modified the variables or prompts, severe security risk. ' .
                                    'Will instead use the defaults from the package.xml');
                                $prompts = $group['param'];
                            }
                        }
                    }

                    $answers = $this->confirmDialog($prompts);
                } else {
                    $answers = $this->confirmDialog($group['param']);
                }
            }

            if ((isset($answers) && $answers) || !isset($group['param'])) {
                if (!isset($answers)) {
                    $answers = array();
                }

                array_unshift($completedPhases, $group['id']);
                if (!$script->run($answers, $group['id'])) {
                    $script->run($completedPhases, '_undoOnError');
                    return;
                }
            } else {
                $script->run($completedPhases, '_undoOnError');
                return;
            }
        }
    }
}
