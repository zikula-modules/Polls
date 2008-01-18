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
    // Initialise table array
    $pntable = array();

    $prefix = pnConfigGetVar('prefix');

    // Table name
    $poll_check = $prefix . '_poll_check';
    $pntable['poll_check'] = $poll_check;
    $pntable['poll_check_column'] = array ('ip'   => 'pn_ip',
                                           'time' => 'pn_time');
    
    $poll_data = $prefix . '_poll_data';
    $pntable['poll_data'] = $poll_data;
    $pntable['poll_data_column'] = array ('pollid'      => 'pn_pollid',
                                          'optiontext'  => 'pn_optiontext',
                                          'optioncount' => 'pn_optioncount',
                                          'voteid'      => 'pn_voteid');
    
    $poll_desc = $prefix . '_poll_desc';
    $pntable['poll_desc'] = $poll_desc;
    $pntable['poll_desc_column'] = array ('pollid'    => 'pn_pollid',
                                          'title'     => 'pn_title',
                                          'timestamp' => 'pn_timestamp',
                                          'voters'    => 'pn_voters',
                                          'planguage' => 'pn_language',
                                          'language'  => 'pn_language');
    
    $pollcomments = $prefix . '_pollcomments';
    $pntable['pollcomments'] = $pollcomments;
    $pntable['pollcomments_column'] = array ('tid'       => 'pn_tid',
                                             'pid'       => 'pn_pid',
                                             'pollid'    => 'pn_pollid',
                                             'date'      => 'pn_date',
                                             'name'      => 'pn_name',
                                             'email'     => 'pn_email',
                                             'url'       => 'pn_url',
                                             'host_name' => 'pn_host_name',
                                             'subject'   => 'pn_subject',
                                             'comment'   => 'pn_comment',
                                             'score'     => 'pn_score',
                                             'reason'    => 'pn_reason');

    return $pntable;
}
