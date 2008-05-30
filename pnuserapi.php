 <?php
/**
 * Polls Module for PostNuke
 *
 * @copyright (c) 2008, Mark West
 * @link http://www.markwest.me.uk
 * @version $Id: pnuserapi.php 20540 2006-11-18 09:53:34Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_3rdParty_Modules
 * @subpackage Polls
*/

/**
 * get all poll items
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
    if (!isset($args['ignoreml']) || !is_bool($args['ignoreml'])) {
        $args['ignoreml'] = false;
    }

    if (!is_numeric($args['startnum']) ||
        !is_numeric($args['numitems'])) {
        return LogUtil::registerError (_MODARGSERROR);
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
    $pntable = pnDBGetTables();
    $polldesccolumn = $pntable['poll_desc_column'];
    $queryargs = array();
    if (pnConfigGetVar('multilingual') == 1 && !$args['ignoreml']) {
        $queryargs[] = "($polldesccolumn[language]='" . DataUtil::formatForStore(pnUserGetLang()) . "' OR $polldesccolumn[language]='')";
    }

    $where = null;
    if (count($queryargs) > 0) {
        $where = ' WHERE ' . implode(' AND ', $queryargs);
    }

    // get the objects from the db
    $items = DBUtil::selectObjectArray('poll_desc', $where, 'pollid', $args['startnum']-1, $args['numitems'], '', $permFilter, $args['catFilter']);

    if($items === false) {
        return LogUtil::registerError (_GETFAILED);
    }

    // need to do this here as the category expansion code can't know the
    // root categories which we need to build the relative paths component
     if (pnModGetVar('Polls', 'enablecategorization') && $items && isset($args['catregistry']) && $args['catregistry']) {
        ObjectUtil::postProcessExpandedObjectArrayCategories ($items, $args['catregistry']);
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
    // optional arguments
    if (isset($args['objectid'])) {
       $args['pollid'] = $args['objectid'];
    }

    // Argument check
    if ((!isset($args['pollid']) || !is_numeric($args['pollid'])) &&
         !isset($args['title'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // define the permission filter to apply
    $permFilter = array();
    $permFilter[] = array('realm' => 0,
                          'component_left' => 'Polls',
                          'instance_left'  => 'title',
                          'instance_right' => 'pollid',
                          'level'          => ACCESS_READ);

    if (isset($args['pollid']) && is_numeric($args['pollid'])) {
        $poll = DBUtil::selectObjectByID('poll_desc', $args['pollid'], 'pollid', '', $permFilter);
    } else {
        $poll = DBUtil::selectObjectByID('poll_desc', $args['title'], 'urltitle', '', $permFilter);
    }
    $poll['options'] = DBUtil::selectObjectArray('poll_data', 'pn_pollid=\''.DataUtil::formatForStore($poll['pollid']).'\'', 'voteid');

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

    if (pnModGetVar('Polls', 'enablecategorization') && !empty($poll['__CATEGORIES__'])) {
        if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
            pn_exit (pnML('_UNABLETOLOADCLASS', array('s' => 'CategoryRegistryUtil')));
        }
        $registeredCats  = CategoryRegistryUtil::getRegisteredModuleCategories('Polls', 'poll_desc');
        ObjectUtil::postProcessExpandedObjectCategories($poll['__CATEGORIES__'], $registeredCats);
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
    // credit to Jorg Napp for this technique - markwest
    $pntable = pnDBGetTables();
    $polldesccolumn = $pntable['poll_desc_column'];
    $queryargs = array();
    if (pnConfigGetVar('multilingual') == 1 && isset($args['ignoreml']) && !$args['ignoreml']) {
        $queryargs[] = "($polldesccolumn[language]='" . DataUtil::formatForStore(pnUserGetLang()) . "' OR $polldesccolumn[language]='')";
    }

    $where = '';
    if (count($queryargs) > 0) {
        $where = ' WHERE ' . implode(' AND ', $queryargs);
    }

    return DBUtil::selectObjectCount('poll_desc', $where, 'pollid', false, $args['catFilter']);
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
            return LogUtil::registerError (_POLLS_VOTEFAILED);
        }

        // add second part of the vote - adds 1 to total vote count
        $sql = "UPDATE $poll_desc_table
               SET $poll_desc_column[voters] = $poll_desc_column[voters]+1
               WHERE $poll_desc_column[pollid] = '" . (int)DataUtil::formatForStore($args['pollid']) . "'";
        $result = DBUtil::executeSQL($sql);
        if (!$result) {
            return LogUtil::registerError (_POLLS_VOTEFAILED);
        }

        // set cookie to indicate vote made in this poll used only with cookie based voting
		// but set all the time in case admin changes voting regs.
        SessionUtil::setVar("poll_voted{$args['pollid']}", 1);
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

/**
 * form custom url string
 *
 * @author Mark West
 * @return string custom url string
 */
function polls_userapi_encodeurl($args)
{
    // check we have the required input
    if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // create an empty string ready for population
    $vars = '';

    // view function
    if ($args['func'] == 'view' && isset($args['args']['cat'])) {
        $vars = substr($args['args']['cat'], 1);
    }

    // for the display function use either the title (if present) or the page id
    if ($args['func'] == 'display' || $args['func'] == 'results') {
        // check for the generic object id parameter
        if (isset($args['args']['objectid'])) {
            $args['args']['pollid'] = $args['args']['objectid'];
        }
        // get the item (will be cached by DBUtil)
        if (isset($args['args']['pollid'])) {
            $item = pnModAPIFunc('Polls', 'user', 'get', array('pollid' => $args['args']['pollid']));
        } else {
            $item = pnModAPIFunc('Polls', 'user', 'get', array('title' => $args['args']['title']));
        }
        if (pnModGetVar('Polls', 'addcategorytitletopermalink') && isset($args['args']['cat'])) {
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
 * @author Mark West
 * @return bool true if successful, false otherwise
 */
function Polls_userapi_decodeurl($args)
{
    // check we actually have some vars to work with...
    if (!isset($args['vars'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // define the available user functions
    $funcs = array('main', 'view', 'display', 'results', 'vote');
    // set the correct function name based on our input
    if (empty($args['vars'][2])) {
        pnQueryStringSetVar('func', 'main');
    } elseif (!in_array($args['vars'][2], $funcs)) {
        pnQueryStringSetVar('func', 'display');
        $nextvar = 2;
    } else {
        pnQueryStringSetVar('func', $args['vars'][2]);
        $nextvar = 3;
    }

    $func = FormUtil::getPassedValue('func');

    // add the category info
    if ($func == 'view') {
        pnQueryStringSetVar('cat', (string)$args['vars'][$nextvar]);
    }

    // identify the correct parameter to identify the page
    if ($func == 'display' || $func == 'results') {
        // get rid of unused vars
        $args['vars'] = array_slice($args['vars'], $nextvar);
        $nextvar = 0;
        if (pnModGetVar('Polls', 'addcategorytitletopermalink') && !empty($args['vars'][$nextvar+1])) {
            $varscount = count($args['vars']);
            $category = array_slice($args['vars'], 0, $varscount - 1);
            pnQueryStringSetVar('cat', implode('/', $category));
            array_splice($args['vars'], 0,  $varscount - 1);
        }
        if (is_numeric($args['vars'][$nextvar])) {
            pnQueryStringSetVar('pollid', $args['vars'][$nextvar]);
        } else {
            pnQueryStringSetVar('title', $args['vars'][$nextvar]);
        }
    }

    return true;
}

/**
 * get meta data for the module
 *
 */
function polls_userapi_getmodulemeta()
{
   return array('viewfunc'    => 'view',
                'displayfunc' => 'display',
                'newfunc'     => 'new',
                'createfunc'  => 'create',
                'modifyfunc'  => 'modify',
                'updatefunc'  => 'update',
                'deletefunc'  => 'delete',
                'titlefield'  => 'title',
                'itemid'      => 'pollid');
}
