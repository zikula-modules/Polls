<?php
/**
 * Polls Module for Zikula
 *
 * @copyright (c) 2008, Mark West
 * @link http://www.markwest.me.uk
 * @version $Id: pninit.php 20616 2006-11-25 15:51:52Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Polls
*/

/**
 * init polls module
 *
 * @author Mark West
 * @return bool true if successful, false otherwise
 */
function Polls_init()
{
    $dom = ZLanguage::getModuleDomain('Polls');
    $tables = array('poll_check', 'poll_data', 'poll_desc');
    foreach ($tables as $table) {
        if (!DBUtil::createTable($table)) {
            return false;
        }
    }

    // create our default category
    if (!_polls_createdefaultcategory()) {
        return LogUtil::registerError (__('Error! Creation attempt failed.', $dom));
    }

    // Set up module variables
    pnModSetVar('Polls', 'itemsperpage', 25);
    pnModSetVar('Polls', 'scale', 1);
    pnModSetVar('Polls', 'sortorder', 0);
    pnModSetVar('Polls', 'enablecategorization', 1);
    pnModSetVar('Polls', 'addcategorytitletopermalink', true);

    // Initialisation successful
    return true;
}

/**
 * upgrade polls module
 *
 * @author Mark West
 * @return bool true if successful, false otherwise
 */
function Polls_upgrade($oldversion)
{
    $dom = ZLanguage::getModuleDomain('Polls');
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
            if (!pnModAvailable('EZComments')) {
                LogUtil::registerError (__('Error! EZComments module not available - this is required to migrate any poll comments', $dom));
                return '1.1';
            }
            // migrate the comments to ezcomments
            // and drop the comments table if successful
            if (pnModAPIFunc('EZComments', 'migrate', 'polls')) {
                if (!DBUtil::dropTable('poll_comments')) {
                    return '1.1';
                }
            }
            pnModSetVar('Polls', 'itemsperpage', 25);
            pnModSetVar('Polls', 'scale', pnConfigGetVar('BarScale'));
            pnConfigDelVar('pollcomm');
            pnConfigDelVar('BarScale');
            // create indexes
            DBUtil::createIndex('pn_ip', 'poll_check', 'ip');
            DBUtil::createIndex('pn_pollid', 'poll_data', 'pollid');

        case '1.2':

        case '2.0':
            pnModSetVar('Polls', 'enablecategorization', true);
            pnModSetVar('Polls', 'addcategorytitletopermalink', true);
            pnModDBInfoLoad('Polls', 'Polls', true);
            if (!_polls_createdefaultcategory()) {
                LogUtil::registerError (__('Error! Update attempt failed.', $dom));
                return '2.0';
            }
        case '2.0.1':
        case '2.0.2':
            _upgrade_updatePollsLanguages();

        case '2.1':
            // future upgrade routines
            break;
    }
    // Upgrade successful
    return true;
}

/**
 * delete the polls module
 *
 * @author Mark West
 * @return bool true if successful, false otherwise
 */
function Polls_delete()
{
    $tables = array('poll_check', 'poll_data', 'poll_desc');
    foreach ($tables as $table) {
        if (!DBUtil::dropTable($table)) {
            return false;
        }
    }

    // Delete module variables
    pnModDelVar('Polls');

    // Deletion successful
    return true;
}

/**
 * create the default categories for this module
 *
 * @author Mark West
 * @return bool true if successful, false otherwise
 */
function _polls_createdefaultcategory($regpath = '/__SYSTEM__/Modules/Global')
{
    // load necessary classes
    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    // get the language file
    $lang = ZLanguage::getLanguageCode();

    // get the category path for which we're going to insert our place holder category
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');
    $pCat    = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Polls');

    if (!$pCat) {
        // create placeholder for all our migrated categories
        $cat = new PNCategory ();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', 'Polls');
        $cat->setDataField('display_name', array($lang => __('Poll Name', $dom)));
        $cat->setDataField('display_desc', array($lang => __('Polls', $dom)));
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
        $registry = new PNCategoryRegistry();
        $registry->setDataField('modname', 'Polls');
        $registry->setDataField('table', 'poll_desc');
        $registry->setDataField('property', 'Main');
        $registry->setDataField('category_id', $rootcat['id']);
        $registry->insert();
    }

    return true;
}

function _upgrade_updatePollsLanguages()
{
    $obj = DBUtil::selectObjectArray('poll_desc');

    if (count($obj) == 0) {
        // nothing to do
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
