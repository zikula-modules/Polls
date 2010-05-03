<?php
/**
 * Polls Module for Zikula
 *
 * @copyright (c) 2010, Mark West
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

/**
 * create a new Polls item
 * @param string $args['title'] name of the item
 * @param string $args['language'] language of the item
 * @param array $args['options'] options for poll
 * @return int Polls item ID on success, false on failure
 */
function Polls_adminapi_create($args)
{
    $dom = ZLanguage::getModuleDomain('Polls');
    // Argument check
    if (!isset($args['title']) || !isset($args['options'])) {
        return LogUtil::registerArgsError();
    }

    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::', "$args[title]::", ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    // defaults
    if (!isset($args['language'])) {
        $args['language'] = '';
    }

    // define the permalink title if not present
    if (!isset($args['urltitle']) || empty($args['urltitle'])) {
        $args['urltitle'] = DataUtil::formatPermalink($args['title']);
    }

    // create the poll
    if (!DBUtil::insertObject($args, 'poll_desc', 'pollid')) {
        return LogUtil::registerError (__('Error! Creation attempt failed.', $dom));
    }

    for ($count = 0; $count <= (sizeof($args['options'])-1); $count++) {
        $item = array('pollid' => $args['pollid'], 'optiontext' => $args['options'][$count+1], 'optioncount' => 0, 'voteid' => $count);
        if (!DBUtil::insertObject($item, 'poll_data')) {
            return LogUtil::registerError (__('Error! Creation attempt failed.', $dom));
        }
    }

    // Let any hooks know that we have created a new item
    pnModCallHooks('item', 'create', $args['pollid'], array('module' => 'Polls'));

    // Return the id of the newly created item to the calling process
    return $args['pollid'];
}

/**
 * delete a Polls item
 * @param $args['pollid'] ID of the item
 * @return bool true on success, false on failure
 * @author Mark West
 */
function Polls_adminapi_delete($args)
{
    $dom = ZLanguage::getModuleDomain('Polls');
    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // Get the poll
    $item = pnModAPIFunc('Polls', 'user', 'get', array('pollid' => $args['pollid']));

    if ($item == false) {
        return LogUtil::registerError (__('Erro! No such item found.', $dom));
    }

    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::Item', "$item[title]::$args[pollid]", ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    // Delete the object
    if (!DBUtil::deleteObjectByID('poll_data', $args['pollid'], 'pollid')) {
        return LogUtil::registerError (__('Error! Deletion attempt failed.', $dom));
    }
    if (!DBUtil::deleteObjectByID('poll_desc', $args['pollid'], 'pollid')) {
        return LogUtil::registerError (__('Error! Deletion attempt failed.', $dom));
    }

    // Let any hooks know that we have deleted an item
    pnModCallHooks('item', 'delete', $args['pollid'], array('module' => 'Polls'));

    // Let the calling process know that we have finished successfully
    return true;
}

/**
 * update a Polls item
 * @param int $args['pollid'] the ID of the item
 * @param string $args['polltitle'] the new name of the item
 * @param string $args['polllanguage'] the new language of the item
 * @param array $args['polloptions'] the new options for the poll
 * @author Mark West
 */
function Polls_adminapi_update($args)
{
    $dom = ZLanguage::getModuleDomain('Polls');
    // Argument check
    if (!isset($args['pollid']) ||
        !isset($args['title']) ||
        !isset($args['options'])) {
        return LogUtil::registerArgsError();
    }

    // set some defaults
    if (!isset($args['language'])) {
        $args['language'] = '';
    }
    if (!isset($args['urltitle']) || empty($args['urltitle'])) {
        $args['urltitle'] = DataUtil::formatPermalink($args['title']);
    }

    // Get the current poll
    $item = pnModAPIFunc('Polls', 'user', 'get', array('pollid' => $args['pollid']));

    if ($item == false) {
        return LogUtil::registerError (__('Error! No such item found.', $dom));
    }

    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::Item', "$item[title]::$args[pollid]", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }
    if (!SecurityUtil::checkPermission( 'Polls::Item', "$args[title]::$args[pollid]", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    if (!DBUtil::updateObject($args, 'poll_desc', '', 'pollid')) {
        return LogUtil::registerError (__('Error! Update attempt failed.', $dom));
    }

    for ($count = 0; $count <= (sizeof($args['options'])-1); $count++) {
        $item = array('pollid' => $args['pollid'], 'optiontext' => $args['options'][$count+1], 'optioncount' => 0, 'voteid' => $count);
        $where = 'WHERE pn_voteid = \''.DataUtil::formatForOS($count) . '\' AND pn_pollid = \'' . DataUtil::formatForOS($args['pollid']) . '\'';
        if (!DBUtil::updateObject($item, 'poll_data', $where)) {
            return LogUtil::registerError (__('Error! Update attempt failed.', $dom));
        }
    }

    // Let any hooks know that we have updated an item.
    pnModCallHooks('item', 'update', $args['pollid'], array('module' => 'Polls'));

    // Let the calling process know that we have finished successfully
    return true;
}

/**
 * get available admin panel links
 *
 * @author Mark West
 * @return array array of admin links
 */
function polls_adminapi_getlinks()
{
    $dom = ZLanguage::getModuleDomain('Polls');
    $links = array();

    if (SecurityUtil::checkPermission('Polls::', '::', ACCESS_READ)) {
        $links[] = array('url' => pnModURL('Polls', 'admin', 'view'), 'text' => __('View polls', $dom));
    }
    if (SecurityUtil::checkPermission('Polls::', '::', ACCESS_ADD)) {
        $links[] = array('url' => pnModURL('Polls', 'admin', 'new'), 'text' => __('Create new poll', $dom));
    }
    if (SecurityUtil::checkPermission('Polls::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => pnModURL('Polls', 'admin', 'modifyconfig'), 'text' => __('Settings', $dom));
    }

    return $links;
}
