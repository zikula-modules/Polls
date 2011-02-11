<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
 
 class Polls_Installer extends Zikula_Installer
 {
    public function install()
    {
        $tables = array('poll_check', 'poll_data', 'poll_desc');
        foreach ($tables as $table) {
            if (!DBUtil::createTable($table)) {
                return false;
            }
        }

        // create our default category
        if (!$this->_createdefaultcategory()) {
            return LogUtil::registerError ($this->__('Error! Creation attempt failed.'));
        }
        
        // Set up module variables
        $vars = array(
            'itemsperpage' => 25,
            'scale' => 1,
            'recurrence' => 0,
            'sortorder' => 0,
            'enablecategorization' => 1,
            'addcategorytitletopermalink' => true
        );
        $this->setVars($vars);

        // Initialisation successful
        return true;
    }
    
    public function upgrade($oldversion)
    {
        // update tables
        $tables = array('poll_check', 'poll_data', 'poll_desc');
        foreach ($tables as $table) {
            if (!DBUtil::changeTable($table)) {
                return false;
            }
        }

        switch ($oldversion) {
            case '1.1':
                // check for the ezcomments module
                if (!ModUtil::available('EZComments')) {
                    LogUtil::registerError ($this->__('Error! EZComments module not available - this is required to migrate any poll comments.'));
                    return '1.1';
                }
                // migrate the comments to ezcomments
                // and drop the comments table if successful
                if (ModUtil::apiFunc('EZComments', 'migrate', 'polls')) {
                    if (!DBUtil::dropTable('poll_comments')) {
                        return '1.1';
                    }
                }
                $this->setVar('itemsperpage', 25);
                $this->setVar('scale', System::getVar('BarScale'));
                System::delVar('pollcomm');
                System::delVar('BarScale');
                // create indexes
                DBUtil::createIndex('pn_ip', 'poll_check', 'ip');
                DBUtil::createIndex('pn_pollid', 'poll_data', 'pollid');

            case '1.2':

            case '2.0':
                $this->setVar('enablecategorization', true);
                $this->setVar('addcategorytitletopermalink', true);
                ModUtil::dbInfoLoad('Polls', 'Polls', true);
                if (!$this->_createdefaultcategory()) {
                    LogUtil::registerError ($this->__('Error! Update attempt failed.'));
                    return '2.0';
                }
            case '2.0.1':
            case '2.0.2':
                $this->_upgrade_updatePollsLanguages();

            case '2.1.0':
                $this->setVar('recurrence', 0);
                
            case '3.0.0':
                // future upgrade routines
                break;
        }
        
        // Upgrade successful
        return true;
    }
    
    public function uninstall()
    {
        $tables = array('poll_check', 'poll_data', 'poll_desc');
        foreach ($tables as $table) {
            if (!DBUtil::dropTable($table)) {
                return false;
            }
        }

        // Delete module variables
        $this->delVar('Polls');

        // Deletion successful
        return true;
    }
    
    /**
     * create the default categories for this module
     *
     * @return bool true if successful, false otherwise
     */
    private function _createdefaultcategory($regpath = '/__SYSTEM__/Modules/Global')
    {
        // get the language file
        $lang = ZLanguage::getLanguageCode();

        // get the category path for which we're going to insert our place holder category
        $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');
        $pCat    = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Polls');

        if (!$pCat) {
            // create placeholder for all our migrated categories
            $cat = new Categories_DBObject_Category();
            $cat->setDataField('parent_id', $rootcat['id']);
            $cat->setDataField('name', 'Polls');
            $cat->setDataField('display_name', array($lang => $this->__('Polls')));
            $cat->setDataField('display_desc', array($lang => $this->__('Voting System Module')));
            if (!$cat->validate('admin')) {
                return false;
            }
            $cat->insert();
            $cat->update();
        }

        // get the category path for which we're going to insert our upgraded categories
        $rootcat = CategoryUtil::getCategoryByPath($regpath);
        if ($rootcat) {
            // create an entry in the categories registry
            $registry = new Categories_DBObject_Registry();
            $registry->setDataField('modname', 'Polls');
            $registry->setDataField('table', 'poll_desc');
            $registry->setDataField('property', 'Main');
            $registry->setDataField('category_id', $rootcat['id']);
            $registry->insert();
        } else {
            return false;
        }

        return true;
    }
    
    private function _upgrade_updatePollsLanguages()
    {
        $obj = DBUtil::selectObjectArray('poll_desc');

        if (count($obj) == 0) {
            return;
        }

        foreach ($obj as $pollid) {
            // translate l3 -> l2
            if ($l2 = ZLanguage::translateLegacyCode($pollid['language'])) {
                $pollid['language'] = $l2;
            }
            DBUtil::updateObject($pollid, 'poll_desc', '', 'pollid', true);
        }

        return true;
    }
 }
