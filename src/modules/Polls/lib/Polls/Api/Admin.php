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

class Polls_Api_Admin extends Zikula_Api
{
    /**
     * create a new Polls item
     * @param string $args['title'] name of the item
     * @param string $args['language'] language of the item
     * @param array $args['options'] options for poll
     * @return int Polls item ID on success, false on failure
     */
    public function create($args)
    {
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
            return LogUtil::registerError ($this->__('Error! Creation attempt failed.'));
        }

        for ($count = 0; $count <= (sizeof($args['options'])-1); $count++) {
            $item = array('pollid' => $args['pollid'], 'optiontext' => $args['options'][$count+1], 'optioncount' => 0, 'voteid' => $count);
            if (!DBUtil::insertObject($item, 'poll_data')) {
                return LogUtil::registerError ($this->__('Error! Creation attempt failed.'));
            }
        }

        // Return the id of the newly created item to the calling process
        return $args['pollid'];
    }
    
    /**
     * update a Polls item
     * @param int $args['pollid'] the ID of the item
     * @param string $args['polltitle'] the new name of the item
     * @param string $args['polllanguage'] the new language of the item
     * @param array $args['polloptions'] the new options for the poll
     */
    public function update($args)
    {
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
        $item = ModUtil::apiFunc('Polls', 'user', 'get', array('pollid' => $args['pollid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('Error! No such poll found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission( 'Polls::Item', "$item[title]::$args[pollid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::checkPermission( 'Polls::Item', "$args[title]::$args[pollid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        if (!DBUtil::updateObject($args, 'poll_desc', '', 'pollid')) {
            return LogUtil::registerError ($this->__('Error! Update attempt failed.'));
        }
        
        $table = DBUtil::getTables();
        $polldata_column = $table['poll_data_column'];
        
        for ($count = 0; $count <= (sizeof($args['options'])-1); $count++) {
            $item = array('pollid' => $args['pollid'], 'optiontext' => $args['options'][$count+1], 'optioncount' => 0, 'voteid' => $count);
            $where = "WHERE " . $polldata_column['voteid'] . " = '" . DataUtil::formatForOS($count) . "' AND " . $polldata_column['pollid'] . " = '" . DataUtil::formatForOS($args['pollid']) . "'";
            if (!DBUtil::updateObject($item, 'poll_data', $where)) {
                return LogUtil::registerError ($this->__('Error! Update attempt failed.'));
            }
        }

        // Let the calling process know that we have finished successfully
        return true;
    }
    
    /**
     * delete a Polls item
     * @param $args['pollid'] ID of the item
     * @return bool true on success, false on failure
     */
    public function delete($args)
    {
        // Argument check
        if (!isset($args['pollid'])) {
            return LogUtil::registerArgsError();
        }

        // Get the poll
        $item = ModUtil::apiFunc('Polls', 'user', 'get', array('pollid' => $args['pollid']));

        if ($item == false) {
            return LogUtil::registerError ($this->__('Error! No such poll found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission( 'Polls::Item', "$item[title]::$args[pollid]", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // Delete the object
        if (!DBUtil::deleteObjectByID('poll_data', $args['pollid'], 'pollid')) {
            return LogUtil::registerError ($this->__('Error! Deletion attempt failed.'));
        }
        if (!DBUtil::deleteObjectByID('poll_desc', $args['pollid'], 'pollid')) {
            return LogUtil::registerError ($this->__('Error! Deletion attempt failed.'));
        }

        // Let the calling process know that we have finished successfully
        return true;
    }
    
    /**
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('Polls::', '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('Polls', 'admin', 'view'), 'text' => $this->__('View polls'));
        }
        if (SecurityUtil::checkPermission('Polls::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('Polls', 'admin', 'newitem'), 'text' => $this->__('Create new poll'));
        }
        if (SecurityUtil::checkPermission('Polls::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Polls', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'));
        }

        return $links;
    }
}
