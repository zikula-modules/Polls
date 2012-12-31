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

/**
 * Populate tables array
 *
 * @return array tables array
 */
function Polls_tables()
{
    $table = array();

    // voting check table
    $table['poll_check'] = DBUtil::getLimitedTablename('poll_check');
    $table['poll_check_column']     = array('ip'   => 'pn_ip',
                                            'time' => 'pn_time');
    $table['poll_check_column_def'] = array('ip'   => "C(20) NOTNULL DEFAULT ''",
                                            'time' => "C(14) NOTNULL DEFAULT ''");
    $table['poll_check_column_idx'] = array('ip' => 'ip');

    // option data table
    $table['poll_data'] = DBUtil::getLimitedTablename('poll_data');
    $table['poll_data_column']      = array('pollid'      => 'pn_pollid',
                                            'optiontext'  => 'pn_optiontext',
                                            'optioncount' => 'pn_optioncount',
                                            'voteid'      => 'pn_voteid');
    $table['poll_data_column_def']  = array('pollid'      => "I NOTNULL DEFAULT '0'",
                                            'optiontext'  => "C(255) NOTNULL DEFAULT ''",
                                            'optioncount' => "I NOTNULL DEFAULT '0'",
                                            'voteid'      => "I NOTNULL DEFAULT '0'");
    $table['poll_data_column_idx']  = array('pollid' => 'pollid');

    // desc table
    $table['poll_desc'] = DBUtil::getLimitedTablename('poll_desc');
    $table['poll_desc_column']      = array('pollid'    => 'pn_pollid',
                                            'title'     => 'pn_title',
                                            'urltitle'  => 'pn_urltitle',
                                            'timestamp' => 'pn_timestamp',
                                            'voters'    => 'pn_voters',
                                            'language'  => 'pn_language');
    $table['poll_desc_column_def']  = array('pollid'    => 'I NOTNULL AUTOINCREMENT PRIMARY',
                                            'title'     => "C(255) NOTNULL DEFAULT ''",
                                            'urltitle'  => "X NOTNULL DEFAULT ''",
                                            'timestamp' => "I NOTNULL DEFAULT '0'",
                                            'voters'    => "I4 NOTNULL DEFAULT '0'",
                                            'language'  => "C(30) NOTNULL DEFAULT ''");
    
    // Enable categorization services
    $table['poll_desc_db_extra_enable_categorization'] = ModUtil::getVar('Polls', 'enablecategorization');
    $table['poll_desc_primary_key_column'] = 'pollid';

    // add standard data fields
    ObjectUtil::addStandardFieldsToTableDefinition ($table['poll_desc_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($table['poll_desc_column_def']);

    // old tables for upgrade/renaming purposes
    $table['pollcomments'] = DBUtil::getLimitedTablename('pollcomments');

    return $table;
}
