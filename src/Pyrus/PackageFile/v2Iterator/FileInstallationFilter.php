<?php
class PEAR2_Pyrus_PackageFile_v2Iterator_FileInstallationFilter extends
    PEAR2_Pyrus_PackageFile_v2Iterator_FileAttribsFilter
{
    static private $_parent;
    static private $_installGroup;
    static function setParent(PEAR2_Pyrus_PackageFile_v2 $parent)
    {
        self::$_parent = $parent;
        $depchecker = new PEAR2_Pyrus_Dependency_Validator(PEAR2_Pyrus_Config::current(), array(),
            array('channel' => self::$_parent->channel,
                  'package' => self::$_parent->package),
            PEAR2_Pyrus_Validate::INSTALLING);
        foreach (self::$_parent->installGroup as $instance) {
            try {
                if (isset($instance['installconditions'])) {
                    $installconditions = $instance['installconditions'];
                    if (is_array($installconditions)) {
                        foreach ($installconditions as $type => $conditions) {
                            if (!isset($conditions[0])) {
                                $conditions = array($conditions);
                            }
                            foreach ($conditions as $condition) {
                                $ret = $depchecker->{"validate{$type}Dependency"}($condition);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // can't use this release
                continue;
            }
            $release = array('install' => array(), 'ignore' => array());
            // this is the release to use
            if (isset($instance['filelist'])) {
                // ignore files
                if (isset($instance['filelist']['ignore'])) {
                    $ignore = isset($instance['filelist']['ignore'][0]) ?
                        $instance['filelist']['ignore'] :
                        array($instance['filelist']['ignore']);
                    foreach ($ignore as $ig) {
                        $release['ignore'][$ig['attribs']['name']] = true;
                    }
                }
                // install files as this name
                if (isset($instance['filelist']['install'])) {
                    $installas = isset($instance['filelist']['install'][0]) ?
                        $instance['filelist']['install'] :
                        array($instance['filelist']['install']);
                    foreach ($installas as $as) {
                        $release['install'][$as['attribs']['name']] =
                            $as['attribs']['as'];
                    }
                }
            }
            self::$_installGroup = $release;
            return;
        }
    }

    function accept()
    {
        if (parent::accept()) {
            if ($this->getInnerIterator()->key() != 'file') {
                return true;
            }
            $curfile = $this->getInnerIterator()->current();
            if (isset($curfile[0])) {
                return true;
            }
            if (isset(self::$_installGroup['ignore'][$curfile->dir . $curfile->name])) {
                // skip ignored files
                return false;
            }
            if (isset(self::$_installGroup['install'][$curfile->dir . $curfile->name])) {
                // add the install-as attribute for these files
                $curfile->{'install-as'} =
                    self::$_installGroup['install'][$curfile->dir . $curfile->name];
            }
            return true;
        }
        return false;
    }
}