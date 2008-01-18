<?php
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2002, PostNuke Development Team
 * @link http://www.postnuke.com
 * @version $Id: pntables.php 19262 2006-06-12 14:45:18Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_Value_Addons
 * @subpackage Polls
*/

/**
 * Populate pntables array
 * @author Xiaoyu Huang
 * @return array pntables array
 */
function Polls_pntables()
{
    $pntable = array();

    // voting check table
    $pntable['poll_check'] = DBUtil::getLimitedTablename('poll_check');
    $pntable['poll_check_column'] = array ('ip'   => 'pn_ip',
                                           'time' => 'pn_time');
    $pntable['poll_check_column_def'] = array('ip'   => "C(20) NOTNULL DEFAULT ''",
                                              'time' => "C(14) NOTNULL DEFAULT ''");
    $pntable['poll_check_column_idx'] = array ('ip' => 'ip');

    // option data table
    $pntable['poll_data'] = DBUtil::getLimitedTablename('poll_data');
    $pntable['poll_data_column'] = array ('pollid'      => 'pn_pollid',
                                          'optiontext'  => 'pn_optiontext',
                                          'optioncount' => 'pn_optioncount',
                                          'voteid'      => 'pn_voteid');
    $pntable['poll_data_column_def'] = array ('pollid'      => "I NOTNULL DEFAULT '0'",
                                              'optiontext'  => "C(50) NOTNULL DEFAULT ''",
                                              'optioncount' => "I NOTNULL DEFAULT '0'",
                                              'voteid'      => "I NOTNULL DEFAULT '0'");
    $pntable['poll_data_column_idx'] = array ('pollid' => 'pollid');

    $pntable['poll_desc'] = DBUtil::getLimitedTablename('poll_desc');
    $pntable['poll_desc_column'] = array ('pollid'    => 'pn_pollid',
                                          'title'     => 'pn_title',
                                          'timestamp' => 'pn_timestamp',
                                          'voters'    => 'pn_voters',
                                          'language'  => 'pn_language');
    $pntable['poll_desc_column_def'] = array ('pollid'    => 'I NOTNULL AUTOINCREMENT PRIMARY',
                                              'title'     => "C(100) NOTNULL DEFAULT ''",
                                              'timestamp' => "I NOTNULL DEFAULT '0'",
                                              'voters'    => "I4 NOTNULL DEFAULT '0'",
                                              'language'  => "C(30) NOTNULL DEFAULT ''");
    
    // Enable categorization services
    $pntable['poll_desc_db_extra_enable_categorization'] = pnModGetVar('Polls', 'enablecategorization');
    $pntable['poll_desc_primary_key_column'] = 'pollid';

    // add standard data fields
    ObjectUtil::addStandardFieldsToTableDefinition ($pntable['poll_desc_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['poll_desc_column_def']);

    // old tables for upgrade/renaming purposes
    $pntable['pollcomments'] = DBUtil::getLimitedTablename('pollcomments');

    return $pntable;
}
