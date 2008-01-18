<?php
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2002, PostNuke Development Team
 * @link http://www.postnuke.com
 * @version $Id: pninit.php 20616 2006-11-25 15:51:52Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_Value_Addons
 * @subpackage Polls
*/

/**
 * init polls module
 * @author Xiaoyu Huang
 * @return bool true if successful, false otherwise
 */
function Polls_init()
{
    $sql = "pn_ip C(20) NOTNULL DEFAULT '',
            pn_time C(14) NOTNULL DEFAULT ''
           ";

    if (!DBUtil::createTable('poll_check', $sql)) {
        return false;
    }

    // Create indexes
    if (!DBUtil::createIndex('pn_ip', 'poll_check', 'ip')) {
        return false;
    }

    $sql = "pn_pollid I(11) NOTNULL DEFAULT '0',
            pn_optiontext C(50) NOTNULL DEFAULT '',
            pn_optioncount I(11) NOTNULL DEFAULT '0',
            pn_voteid I(11) NOTNULL DEFAULT '0'
          ";

    if (!DBUtil::createTable('poll_data', $sql)) {
        return false;
    }

    // Create indexes
    if (!DBUtil::createIndex('pn_pollid', 'poll_data', 'pollid')) {
        return false;
    }

    $sql = "pn_pollid I(11) NOTNULL AUTOINCREMENT PRIMARY,
            pn_title C(100) NOTNULL DEFAULT '',
            pn_timestamp I(11) NOTNULL DEFAULT '0',
            pn_voters I4(9) NOTNULL DEFAULT '0',
            pn_language C(30) NOTNULL DEFAULT ''
           ";

    if (!DBUtil::createTable('poll_desc', $sql)) {
        return false;
    }

    // Set up module variables
    pnModSetVar('Polls', 'itemsperpage', 25);
    pnModSetVar('Polls', 'scale', 1);

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
    switch ($oldversion) {
        case 1.1:
            // check for the ezcomments module
            if (!pnModAvailable('EZComments')) {
                return LogUtil::registerError (_POLLSNOCOMMENTS);
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
    if (!DBUtil::dropTable('poll_check')) {
        return false;
    }

    if (!DBUtil::dropTable('poll_data')) {
        return false;
    }

    if (!DBUtil::dropTable('poll_desc')) {
        return false;
    }

    // Delete module variables
    pnModDelVar('Polls');

    // Deletion successful
    return true;
}
