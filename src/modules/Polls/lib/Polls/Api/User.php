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
 
class Polls_Api_User extends Zikula_Api
{
    /**
     * get all poll items
     *
     * @return array array of items, or false on failure
     */
    public function getall($args)
    {
        // Optional arguments
        if (!isset($args['startnum']) || !is_numeric($args['startnum'])) {
            $args['startnum'] = 1;
        }
        if (!isset($args['numitems']) || !is_numeric($args['numitems'])) {
            $args['numitems'] = -1;
        }
        if (!isset($args['ignoreml']) || !is_bool($args['ignoreml'])) {
            $args['ignoreml'] = false;
        }

        if (!is_numeric($args['startnum']) ||
            !is_numeric($args['numitems'])) {
            return LogUtil::registerArgsError();
        }

        // create a empty result set
        $items = array();

        // Security check
        if (!SecurityUtil::checkPermission('Polls::', '::', ACCESS_READ)) {
            return $items;
        }

        $args['catFilter'] = array();
        if (isset($args['category']) && !empty($args['category'])){
            if (is_array($args['category'])) {
                $args['catFilter'] = $args['category'];
            } elseif (isset($args['property'])) {
                $property = $args['property'];
                $args['catFilter'][$property] = $args['category'];
            }
        }

        // define the permission filter to apply
        $permFilter = array(array('realm'          => 0,
                                  'component_left' => 'Polls',
                                  'instance_left'  => 'title',
                                  'instance_right' => 'pollid',
                                  'level'          => ACCESS_READ));

        // populate an array with each part of the where clause and then implode the array if there is a need.
        // credit to Jorg Napp for this technique - markwest
        $table = DBUtil::getTables();
        $polldesc_column = $table['poll_desc_column'];
        
        $queryargs = array();
        
        if (system::getVar('multilingual') == 1 && !$args['ignoreml']) {
            $queryargs[] = "($polldesc_column[language] = '" . DataUtil::formatForStore(ZLanguage::getLanguageCode()) . "' OR $polldesc_column[language] = '')";
        }

        $where = null;
        if (count($queryargs) > 0) {
            $where = ' WHERE ' . implode(' AND ', $queryargs);
        }

        $orderby = '';
        // Handle the sort order
        if (!isset($args['order'])) {
            $args['order'] = $this->getVar('sortorder');

            switch ($args['order']) {
                case 0:
                    $order = 'pollid';
                    break;
                case 1:
                default:
                    $order = 'cr_date';
            }
        } else {
            $order = $args['order'];
        }
        if (!empty($order)) {
            $orderby = $polldesc_column[$order].' DESC';
        }


        // get the objects from the db
        $items = DBUtil::selectObjectArray('poll_desc', $where, $orderby, $args['startnum']-1, $args['numitems'], '', $permFilter, $args['catFilter']);

        if($items === false) {
            return LogUtil::registerError ($this->__('Error! Could not load polls.'));
        }

        // need to do this here as the category expansion code can't know the
        // root categories which we need to build the relative paths component
         if ($this->getVar('enablecategorization') && $items && isset($args['catregistry']) && $args['catregistry']) {
            ObjectUtil::postProcessExpandedObjectArrayCategories ($items, $args['catregistry']);
        }

        // Return the items
        return $items;
    }
    
    /**
     * utility function to count the number of items held by this module
     *
     * @return integer number of items held by this module
     */
    public function countitems()
    {
        $args['catFilter'] = array();
        if (isset($args['category']) && !empty($args['category'])){
            if (is_array($args['category'])) {
                $args['catFilter'] = $args['category'];
            } elseif (isset($args['property'])) {
                $property = $args['property'];
                $args['catFilter'][$property] = $args['category'];
            }
        }

        // populate an array with each part of the where clause and then implode the array if there is a need.
        $table = DBUtil::getTables();
        $polldesc_column = $table['poll_desc_column'];
        
        $queryargs = array();
        if (System::getVar('multilingual') == 1 && isset($args['ignoreml']) && !$args['ignoreml']) {
            $queryargs[] = "($polldesc_column[language] = '" . DataUtil::formatForStore(ZLanguage::getLanguageCode()) . "' OR $polldesc_column[language] = '')";
        }

        $where = '';
        if (count($queryargs) > 0) {
            $where = ' WHERE ' . implode(' AND ', $queryargs);
        }

        return DBUtil::selectObjectCount('poll_desc', $where, 'pollid', false, $args['catFilter']);
    }
    
    /**
     * get a specific item
     *
     * @param $args['pollid'] id of poll to get or -1 to get the latest one
     * @return array item array, or false on failure
     */
    public function get($args)
    {
        // optional arguments
        if (isset($args['objectid'])) {
           $args['pollid'] = $args['objectid'];
        }

        // Argument check
        if ((!isset($args['pollid']) || !is_numeric($args['pollid'])) &&
             !isset($args['title'])) {
            return LogUtil::registerArgsError();
        }

        // define the permission filter to apply
        $permFilter = array();
        $permFilter[] = array('realm' => 0,
                              'component_left' => 'Polls',
                              'instance_left'  => 'title',
                              'instance_right' => 'pollid',
                              'level'          => ACCESS_READ);

        if (isset($args['pollid']) && is_numeric($args['pollid'])) {
            if ($args['pollid'] == -1) {
                $poll = DBUtil::selectObjectArray('poll_desc', '', 'pn_cr_date DESC', -1, 1, '', $permFilter);
                if ($poll !== false) {
                    $poll = $poll[0];
                }
            } else {
                $poll = DBUtil::selectObjectByID('poll_desc', $args['pollid'], 'pollid', '', $permFilter);
            }
        } else {
             $poll = DBUtil::selectObjectByID('poll_desc', $args['title'], 'urltitle', '', $permFilter);
        }
        
        $table = DBUtil::getTables();
        $polldata_column = $table['poll_data_column'];

        $poll['options'] = DBUtil::selectObjectArray('poll_data', $polldata_column['pollid'] . ' = ' . DataUtil::formatForStore($poll['pollid']), 'voteid');

        $results = array();
        $count = count($poll['options']);
        $scale = $this->getVar('scale');
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

        if ($this->getVar('enablecategorization') && !empty($poll['__CATEGORIES__'])) {
            $registeredCats  = CategoryRegistryUtil::getRegisteredModuleCategories('Polls', 'poll_desc');
            ObjectUtil::postProcessExpandedObjectCategories($poll['__CATEGORIES__'], $registeredCats);
        }

        // Return the item array
        return $poll;
    }
    
    /**
     * Add vote to db
     *
     * @param int $args['pollid'] poll id
     * @param int $args['voteid'] option voted for
     * @param string $args['polltitle'] title of poll
     */
    public function vote($args)
    {
        // Argument check
        if (!isset($args['pollid']) || !isset($args['voteid']) || !isset($args['title'])) {
            return LogUtil::registerArgsError();
        }

        if (SecurityUtil::checkPermission( 'Polls::', "$args[title]::$args[pollid]", ACCESS_COMMENT)) {
            // define the tables we're working with
            $table = DBUtil::getTables();
            
            $polldata_table = $table['poll_data'];
            $polldata_column = $table['poll_data_column'];
            $polldesc_table = $table['poll_desc'];
            $polldesc_column = $table['poll_desc_column'];

            // add first part of vote - adds 1 to option vote count
            $sql = "
            UPDATE $polldata_table
            SET 
                $polldata_column[optioncount] = $polldata_column[optioncount]+1
            WHERE 
                $polldata_column[pollid] = " . (int)DataUtil::formatForStore($args['pollid']) . "
            AND $polldata_column[voteid] = " . (int)DataUtil::formatForStore($args['voteid']);
            $result = DBUtil::executeSQL($sql);
            
            if (!$result) {
                LogUtil::registerError ($this->__('Error! Error creating vote.'));
                return false;
            }

            // add second part of the vote - adds 1 to total vote count
            $sql = "
            UPDATE $polldesc_table
            SET 
                $polldesc_column[voters] = $polldesc_column[voters]+1
            WHERE $polldesc_column[pollid] = " . (int)DataUtil::formatForStore($args['pollid']);
            $result = DBUtil::executeSQL($sql);
            
            if (!$result) {
                LogUtil::registerError ($this->__('Error! Error creating vote.'));
                return false;
            }

            // set cookie to indicate vote made in this poll used only with cookie based voting
            // but set all the time in case admin changes voting regs.
            SessionUtil::setVar("poll_voted{$args['pollid']}", time());
        }

        return true;
    }
    
    /**
     * utility function to count the number of items held by this module
     *
     * @TODO develop  selectObjectSumByID method and submit to core as patch
     * @return integer number of items held by this module
     */
    public function countvotes($args)
    {
        // Argument check
        if (!isset($args['pollid'])) {
            return LogUtil::registerArgsError();
        }

        // setup where clause
        $table = DBUtil::getTables();
        $polldata_column = $table['poll_data_column'];
        $where = "WHERE $polldata_column[pollid] = ".(int)DataUtil::formatForStore($args['pollid']);

        // Return the vote count
        return (int)DBUtil::selectObjectSum('poll_data', 'optioncount', $where);
    }
    
    /**
     * utility function to find out if user is allowed to vote (based on session variable and corresponding settings in module config)
     *
     * @return boolean true/false
     */
    public function allowedtovote($args)
    {
        $pollid = $args['pollid'];
        $session = SessionUtil::getVar("poll_voted".$pollid);
        
        if (empty($session)) {
            return true;
        }
        
        $current_timestamp = time();
        $vote_timestamp = $session;
        $difference = $current_timestamp - $vote_timestamp;
        
        $recurrence = $this->getVar('recurrence');
        switch($recurrence)
        {
            case -1:
                $result = true;
                break;
                
            case 0:
                $result = empty($session) ? true : false;
                break;
                
            case 1:
                $target_difference = 60 * 60 * 24;
                $result = ($difference > $target_difference) ? true : false;
                break;
                
            case 7:
                $target_difference = 60 * 60 * 24 * 7;
                $result = ($difference > $target_difference) ? true : false;
                break;
                
            case 31:
                $target_difference = 60 * 60 * 24 * 7 * date('t');
                $result = ($difference > $target_difference) ? true : false;
                break;
        }
        
        return $result;
    }
    
    /**
     * form custom url string
     *
     * @return string custom url string
     */
    public function encodeurl($args)
    {
        // check we have the required input
        if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
            return LogUtil::registerArgsError();
        }

        // create an empty string ready for population
        $vars = '';

        // add the category name to the view link
        if ($args['func'] == 'view' && isset($args['args']['prop'])) {
            $vars = $args['args']['prop'];
            if (isset($args['args']['cat'])) {
                $vars .= '/'.$args['args']['cat'];
            }
        }

        // for the display function use either the title (if present) or the page id
        if ($args['func'] == 'display' || $args['func'] == 'results') {
            // check for the generic object id parameter
            if (isset($args['args']['objectid'])) {
                $args['args']['pollid'] = $args['args']['objectid'];
            }
            // get the item (will be cached by DBUtil)
            if (isset($args['args']['pollid'])) {
                $item = ModUtil::apiFunc('Polls', 'user', 'get', array('pollid' => $args['args']['pollid']));
            } else {
                $item = ModUtil::apiFunc('Polls', 'user', 'get', array('title' => $args['args']['title']));
            }
            if ($this->getVar('addcategorytitletopermalink') && isset($args['args']['cat'])) {
                $vars = $args['args']['cat'].'/'.$item['urltitle'];
            } else {
                $vars = $item['urltitle'];
            }
            if (isset($args['args']['page']) && $args['args']['page'] != 1) {
                $vars .= '/page/'.$args['args']['page'];
            }
        }
        // don't display the function name if either displaying an page or the normal overview
        if ($args['func'] == 'main' || $args['func'] == 'display') {
            $args['func'] = '';
        }

        // construct the custom url part
        if (empty($args['func']) && empty($vars)) {
            return $args['modname'] . '/';
        } elseif (empty($args['func'])) {
            return $args['modname'] . '/' . $vars . '/';
        } elseif (empty($vars)) {
            return $args['modname'] . '/' . $args['func'] . '/';
        } else {
            return $args['modname'] . '/' . $args['func'] . '/' . $vars . '/';
        }
    }
    
    /**
     * decode the custom url string
     *
     * @return bool true if successful, false otherwise
     */
    public function decodeurl($args)
    {
        // check we actually have some vars to work with...
        if (!isset($args['vars'])) {
            return LogUtil::registerArgsError();
        }

        // define the available user functions
        $funcs = array('main', 'view', 'display', 'results', 'vote');
        
        // set the correct function name based on our input
        if (empty($args['vars'][2])) {
            System::queryStringSetVar('func', 'main');
        } elseif (!in_array($args['vars'][2], $funcs)) {
            System::queryStringSetVar('func', 'display');
            $nextvar = 2;
        } else {
            System::queryStringSetVar('func', $args['vars'][2]);
            $nextvar = 3;
        }

        $func = FormUtil::getPassedValue('func');

        // add the category info
        if ($func == 'view' && isset($args['vars'][$nextvar])) {
            // get rid of unused vars
            $args['vars'] = array_slice($args['vars'], $nextvar);
            System::queryStringSetVar('prop', (string)$args['vars'][0]);

            if (isset ($args['vars'][1])) {
                // check if there's a page arg
                $varscount = count($args['vars']);
                ($args['vars'][$varscount-2] == 'page') ? $pagersize = 2 : $pagersize = 0;
                // extract the category path
                $cat = implode('/', array_slice($args['vars'], 1, $varscount - $pagersize - 1));
                System::queryStringSetVar('cat', $cat);
            }
        }

        // identify the correct parameter to identify the page
        if ($func == 'display' || $func == 'results') {
            // get rid of unused vars
            $args['vars'] = array_slice($args['vars'], $nextvar);
            $nextvar = 0;
            if ($this->getVar('addcategorytitletopermalink') && !empty($args['vars'][$nextvar+1])) {
                $varscount = count($args['vars']);
                $category = array_slice($args['vars'], 0, $varscount - 1);
                System::queryStringSetVar('cat', implode('/', $category));
                array_splice($args['vars'], 0,  $varscount - 1);
            }
            if (is_numeric($args['vars'][$nextvar])) {
                System::queryStringSetVar('pollid', $args['vars'][$nextvar]);
            } else {
                System::queryStringSetVar('title', $args['vars'][$nextvar]);
            }
        }

        return true;
    }
    
    /**
     * get meta data for the module
     *
     */
    public function getmodulemeta()
    {
       return array('viewfunc'    => 'view',
                    'displayfunc' => 'display',
                    'newfunc'     => 'newitem',
                    'createfunc'  => 'create',
                    'modifyfunc'  => 'modify',
                    'updatefunc'  => 'update',
                    'deletefunc'  => 'delete',
                    'titlefield'  => 'title',
                    'itemid'      => 'pollid');
    }
}

