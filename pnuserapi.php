<?php
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2002, PostNuke Development Team
 * @link http://www.postnuke.com
 * @version $Id: pnuserapi.php 20540 2006-11-18 09:53:34Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_Value_Addons
 * @subpackage Polls
*/
/**
 * get all example items
 * @author Mark West
 * @return array array of items, or false on failure
 */
function Polls_userapi_getall($args)
{
    // Optional arguments
    if (!isset($args['startnum']) || !is_numeric($args['startnum'])) {
        $args['startnum'] = 1;
    }
    if (!isset($args['numitems']) || !is_numeric($args['numitems'])) {
        $args['numitems'] = -1;
    }

    $items = array();

    // Security check
    if (!SecurityUtil::checkPermission('Polls::', '::', ACCESS_READ)) {
        return $items;
    }

    // define the permission filter to apply
    $permFilter = array(array('realm'          => 0,
                              'component_left' => 'Polls',
                              'instance_left'  => 'title',
                              'instance_right' => 'pollid',
                              'level'          => ACCESS_READ));

    // get the objects from the db
    $items = DBUtil::selectObjectArray('poll_desc', '', 'pollid', $args['startnum']-1, $args['numitems'], '', $permFilter);

    if($items === false) {
        return LogUtil::registerError (_GETFAILED);
    }

    // Return the items
    return $items;
}

/**
 * get a specific item
 * @param $args['pollid'] id of poll to get
 * @author Mark West
 * @return array item array, or false on failure
 */
function Polls_userapi_get($args)
{
    // Argument check
    if (!isset($args['pollid']) && is_numeric($args['pollid'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // define the permission filter to apply
    $permFilter = array(array('realm'          => 0,
                              'component_left' => 'Polls',
                              'instance_left'  => 'title',
                              'instance_right' => 'pollid',
                              'level'          => ACCESS_READ));


    $poll = DBUtil::selectObjectByID('poll_desc', $args['pollid'], 'pollid', '', $permFilter);
    $poll['options'] = DBUtil::selectObjectArray('poll_data', 'pn_pollid=\''.DataUtil::formatForStore($args['pollid']).'\'', 'voteid');

    $results = array();
    $count = count($poll['options']);
    $scale = pnModGetVar('Polls', 'scale');
    for ($i = 0, $max = $count; $i < $max; $i++) {
        // if the poll option has some text then display its result
        $row = array();
        // calculate vote percentage and scaled percentage for graph
        if ($poll['options'][$i]['optioncount']  != 0) {
            $percent = ($poll['options'][$i]['optioncount'] / $poll['voters']) * 100;
        } else {
            $percent = 0;
        }
        $percentint = (int)$percent;
        $percentintscaled = $percentint * $scale;
        $poll['options'][$i]['percent'] = $percentint;
        $poll['options'][$i]['percentscaled'] = $percentintscaled;
    }

    // Return the item array
    return $poll;
}

/**
 * utility function to count the number of items held by this module
 * @author Mark West
 * @return integer number of items held by this module
 */
function Polls_userapi_countitems()
{
    return DBUtil::selectObjectCount('poll_desc', '');
}

/**
 * Add vote to db
 * @param int $args['pollid'] poll id
 * @param int $args['voteid'] option voted for
 * @param string $args['polltitle'] title of poll
 * @author Mark West
 */
function Polls_userapi_vote($args)
{
    // Argument check
    if (!isset($args['pollid']) || !isset($args['voteid']) || !isset($args['title'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    if (SecurityUtil::checkPermission( 'Polls::', "$args[title]::$args[pollid]", ACCESS_COMMENT)) {
        // define the tables we're working with
        $pntable = pnDBGetTables();
        $poll_data_table = $pntable['poll_data'];
        $poll_data_column = $pntable['poll_data_column'];
        $poll_desc_table = $pntable['poll_desc'];
        $poll_desc_column = $pntable['poll_desc_column'];

        // add first part of vote - adds 1 to option vote count
        $sql = "UPDATE $poll_data_table
                SET $poll_data_column[optioncount] = $poll_data_column[optioncount]+1
                WHERE ($poll_data_column[pollid] = '" . (int)DataUtil::formatForStore($args['pollid']) . "')
                AND ($poll_data_column[voteid] = '" . (int)DataUtil::formatForStore($args['voteid']) . "')";
        $result = DBUtil::executeSQL($sql);
        if (!$result) {
            return LogUtil::registerError (_POLLSVOTEFAILED);
        }

        // add second part of the vote - adds 1 to total vote count
        $sql = "UPDATE $poll_desc_table
               SET $poll_desc_column[voters] = $poll_desc_column[voters]+1
               WHERE $poll_desc_column[pollid] = '" . (int)DataUtil::formatForStore($args['pollid']) . "'";
        $result = DBUtil::executeSQL($sql);
        if (!$result) {
            return LogUtil::registerError (_POLLSVOTEFAILED);
        }

        // set cookie to indicate vote made in this poll used only with cookie based voting
		// but set all the time in case admin changes voting regs.
        pnSessionSetVar("poll_voted" . $args['pollid'], 1);
    }

    return true;
}

/**
 * utility function to count the number of items held by this module
 * @author Mark West
 * @return integer number of items held by this module
 */
function Polls_userapi_countvotes($args)
{
    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // Get table setup
    $pntable = pnDBGetTables();
    $poll_data_table = $pntable['poll_data'];
    $poll_data_column = $pntable['poll_data_column'];

    // Get item
    $sql = "SELECT SUM($poll_data_column[optioncount])
            FROM $poll_data_table
            WHERE $poll_data_column[pollid] = '".(int)DataUtil::formatForStore($args['pollid'])."'";
    $result = DBUtil::executeSQL($sql);

    if (!$result) {
        return false;
    }

    // Obtain the number of items
    list($votecount) = $result->fields;

    $result->Close();

    // Return the number of items
    return $votecount;
}

?>