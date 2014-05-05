<?php
/**
 * \Pyrus\ScriptRunner
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

namespace Pyrus;

/**
 * Post-install script runner for Pyrus
 *
 * This class handles the logic of navigating a post-install script's XML,
 * determining what questions to ask, and then passes the information to the
 * actual class.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class ScriptRunner
{
    protected $frontend;
    static protected $skipSections;

    function __construct($frontend)
    {
        $this->frontend = $frontend;
    }

    function run(PackageFileInterface $package)
    {
        foreach ($package->scriptfiles as $file) {
            $this->runPostinstallScripts($file, $package);
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
    static function skipParamgroup($id)
    {
        self::$skipSections[$id] = true;
    }

    function runPostinstallScripts(PackageFile\v2\Files\File $scriptfile,
                                   PackageFileInterface $package)
    {
        foreach ($scriptfile->postinstallscript as $script) {
            $script->setupPostInstall();
            $this->runInstallScript($script);
        }
    }

    /**
     * @param \Pyrus\Task\Postinstallscript $info contents of postinstallscript tag
     * @param object $script post-installation script
     * @param string install|upgrade
     */
    function runInstallScript(Task\Postinstallscript $info)
    {
        self::$skipSections = array();
        if (!count($info->paramgroup)) {
            $info->scriptobject->run2(array(), '_default');
            return;
        }

        $completedPhases = array();
        try {
            foreach ($info->paramgroup as $group) {
                if (isset(self::$skipSections[$group->id])) {
                    // the post-install script chose to skip this section dynamically
                    continue;
                }

                if (isset($lastgroup)) {
                    if (!isset($answers)) {
                        $answers = null;
                    }

                    if (!$group->matchesConditionType($answers)) {
                        continue;
                    }
                }

                if (isset($group->instructions)) {
                    $this->frontend->display($group->instructions);
                }

                if (isset($answers)) {
                    $oldanswers = $answers;
                    $answers    = array();
                } else {
                    $oldanswers = $answers = array();
                }

                if (isset($group->param)) {
                    if (method_exists($info->scriptobject, 'postProcessPrompts2')) {
                        $prompts = $info->scriptobject->postProcessPrompts2($group->param->getPrompts(), $group->id);
                        if (!is_array($prompts) || count($prompts) != count($group->param)) {
                            throw new Task\Exception('Error: post-install script did not ' .
                                'return proper post-processed prompts');
                        }

                        $testprompts = $group->param->getPrompts();
                        foreach ($prompts as $i => $prompt) {
                            if (!is_array($prompt) || !isset($prompt['prompt']) ||
                                  !isset($prompt['name']) ||
                                  ($prompt['name'] != $testprompts[$i]['name']) ||
                                  ($prompt['type'] != $testprompts[$i]['type'])
                            ) {
                                throw new Task\Exception('Error: post-install script ' .
                                    'modified the variables or prompts, severe security risk');
                            }
                        }

                        $answers = $this->frontend->confirmDialog($prompts);
                    } else {
                        $answers = $this->frontend->confirmDialog($group->param->getPrompts());
                    }
                }

                if ((isset($answers) && $answers) || !isset($group->param)) {
                    if (!isset($answers)) {
                        $answers = array();
                    }

                    array_unshift($completedPhases, $group->id);
                    // script should throw an exception on failure
                    $info->scriptobject->run2(array_merge($answers, $oldanswers), $group->id);
                } else {
                    $info->scriptobject->run2($completedPhases, '_undoOnError');
                    return;
                }

                $answers = $this->mergeOldAnswers($oldanswers, $answers, $group->id);
            }
        } catch (\Exception $e) {
            $info->scriptobject->run2($completedPhases, '_undoOnError');
            throw $e;
        }
    }

    function mergeOldAnswers($answers, $newanswers, $section)
    {
        foreach ($newanswers as $prompt => $answer) {
            $answers[$section . '::' . $prompt] = $answer;
        }

        if (!count($answers)) {
            $answers = null;
        }

        return $answers;
    }
}
