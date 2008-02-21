<?php
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2002, PostNuke Development Team
 * @link http://www.postnuke.com
 * @version $Id: pninit.php 20616 2006-11-25 15:51:52Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_3rdParty_Modules
 * @subpackage Polls
*/

/**
 * init polls module
 * @author Xiaoyu Huang
 * @return bool true if successful, false otherwise
 */
function Polls_init()
{
    $tables = array('poll_check', 'poll_data', 'poll_desc');
    foreach ($tables as $table) {
        if (!DBUtil::createTable($table)) {
            return false;
        }
    }

    // create our default category
    if (!_polls_createdefaultcategory()) {
        return LogUtil::registerError (_CREATEFAILED);
    }

    // Set up module variables
    pnModSetVar('Polls', 'itemsperpage', 25);
    pnModSetVar('Polls', 'scale', 1);
    pnModSetVar('Polls', 'enablecategorization', 1);
    pnModSetVar('Polls', 'addcategorytitletopermalink', true);

    // Initialisation successful
    return true;
}

/**
 * upgrade
 * @author Xiaoyu Huang
 * @return bool true if successful, false otherwise
 */
function Polls_upgrade($oldversion)
{
    // update tables
    $tables = array('poll_check', 'poll_data', 'poll_desc');
    foreach ($tables as $table) {
        if (!DBUtil::changeTable($table)) {
            return false;
        }
    }

    switch ($oldversion) {
        case 1.1:
            // check for the ezcomments module
            if (!pnModAvailable('EZComments')) {
                return LogUtil::registerError (_POLLS_NOEZCOMMENTS);
            }
            // migrate the comments to ezcomments
            // and drop the comments table if successful
            if (pnModAPIFunc('EZComments', 'migrate', 'polls')) {
                if (!DBUtil::dropTable('poll_comments')) {
                    return false;
                }
            }
            pnModSetVar('Polls', 'itemsperpage', 25);
            pnModSetVar('Polls', 'scale', pnConfigGetVar('BarScale'));
            pnConfigDelVar('pollcomm');
            pnConfigDelVar('BarScale');
            // create indexes
            DBUtil::createIndex('pn_ip', 'poll_check', 'ip');
            DBUtil::createIndex('pn_pollid', 'poll_data', 'pollid');
            return polls_upgrade(1.2);
        case 1.2:
            return polls_upgrade(2.0);
        case 2.0:
            pnModSetVar('Polls', 'enablecategorization', true);
            pnModSetVar('Polls', 'addcategorytitletopermalink', true);
            pnModDBInfoLoad('Polls', 'Polls', true);
            if (!_polls_createdefaultcategory()) {
                return LogUtil::registerError (_UPDATEFAILED);
            }
            break;
    }
    // Upgrade successful
    return true;
}

/**
 * delete the polls module
 * @author Xiaoyu Huang
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

function _polls_createdefaultcategory($regpath = '/__SYSTEM__/Modules/Global')
{
    // load necessary classes
    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    // get the language file
    $lang = pnUserGetLang();

    // get the category path for which we're going to insert our place holder category
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');
    $pCat    = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Polls');

    if (!$pCat) {
        // create placeholder for all our migrated categories
        $cat = new PNCategory ();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', 'Polls');
        $cat->setDataField('display_name', array($lang => _POLLS_NAME));
        $cat->setDataField('display_desc', array($lang => _POLLS_CATEGORY_DESCRIPTION));
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
