<?php
/**
 * Polls Module for Zikula
 *
 * @copyright (c) 2008, Mark West
 * @link http://www.markwest.me.uk
 * @version $Id: pnuser.php 20571 2006-11-22 18:25:29Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Polls
*/

/**
 * the main user function
 *
 * @author Mark West
 * @return string HTML string
 */
function Polls_user_main()
{
    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::', '::', ACCESS_READ)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $renderer = pnRender::getInstance('Polls');

    // Return the output that has been generated by this function
    return $renderer->fetch('polls_user_main.htm');
}

/**
 * view items
 * This is a standard function to provide an overview of all of the items
 * available from the module.
 * @author Mark West
 * @return string HTML string
 */
function Polls_user_view()
{
    $dom = ZLanguage::getModuleDomain('Polls');
    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::', '::', ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
    }

    $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');
    $prop     = (string)FormUtil::getPassedValue('prop', isset($args['prop']) ? $args['prop'] : null, 'GET');
    $cat      = (string)FormUtil::getPassedValue('cat', isset($args['cat']) ? $args['cat'] : null, 'GET');

    // defaults and input validation
    if (!is_numeric($startnum) || $startnum < 0) {
        $startnum = 1;
    }

    // get all module vars for later use
    $modvars = pnModGetVar('Polls');

    // check if categorization is enabled
    if ($modvars['enablecategorization']) {
        if (!($class = Loader::loadClass('CategoryUtil')) || !($class = Loader::loadClass('CategoryRegistryUtil'))) {
            pn_exit (__f('Error! Unable to load class [%s%]', 'CategoryUtil | CategoryRegistryUtil', $dom));
        }
        // get the categories registered for the Pages
        $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories('Polls', 'poll_desc');
        $properties = array_keys($catregistry);

        // validate the property
        // and build the category filter - mateo
        if (!empty($prop) && in_array($prop, $properties)) {
            // if the property and the category are specified
            // means that we'll list the pages that belongs to that category
            if (!empty($cat)) {
                if (!is_numeric($cat)) {
                    $rootCat = CategoryUtil::getCategoryByID($catregistry[$prop]);
                    $cat = CategoryUtil::getCategoryByPath($rootCat['path'].'/'.$cat);
                } else {
                    $cat = CategoryUtil::getCategoryByID($cat);
                }
                if (!empty($cat) && isset($cat['path'])) {
                    // include all it's subcategories and build the filter
                    $categories = categoryUtil::getCategoriesByPath($cat['path'], '', 'path');
                    $catstofilter = array();
                    foreach ($categories as $category) {
                        $catstofilter[] = $category['id'];
                    }
                    $catFilter = array($prop => $catstofilter);
                } else {
                    LogUtil::registerError(__('Error! Invalid category', $dom));
                }
            }
        }
    }

    // Get all the polls
    $items = pnModAPIFunc('Polls', 'user', 'getall',
                          array('startnum' => $startnum,
                                'numitems' => $modvars['itemsperpage'],
                                'category' => isset($catFilter) ? $catFilter : null,
                                'catregistry' => isset($catregistry) ? $catregistry : null));

    if ($items == false) {
        LogUtil::registerError(__('No items found.', $dom));
    }

    // Create output object
    $renderer = pnRender::getInstance('Polls');

    // Loop through each item and display it
    $polls = array();
    foreach ($items as $item) {
        if (SecurityUtil::checkPermission('Polls::', "$item[title]::$item[pollid]", ACCESS_READ)) {
            $item['votecount'] = pnModAPIFunc('Polls', 'user', 'countvotes', array('pollid' => $item['pollid']));
            $renderer->assign($item);
            $polls[] = $renderer->fetch('polls_user_row.htm', $item['pollid']);
        }
    }
    $renderer->assign('polls', $polls);

    // assign various useful template variables
    $renderer->assign('startnum', $startnum);
    $renderer->assign('lang', ZLanguage::getLanguageCode());
    $renderer->assign($modvars);
    $renderer->assign('shorturls', pnConfigGetVar('shorturls'));
    $renderer->assign('shorturlstype', pnConfigGetVar('shorturlstype'));

    // Assign the values for the smarty plugin to produce a pager
    $renderer->assign('pager', array('numitems' => pnModAPIFunc('Polls', 'user', 'countitems', array('category' => isset($catFilter) ? $catFilter : null)),
                                     'itemsperpage' => $modvars['itemsperpage']));

    // Return the output that has been generated by this function
    return $renderer->fetch('polls_user_view.htm');
}

/**
 * display item
 * This is a standard function to provide detailed informtion on a single item
 * available from the module.
 * @author Mark West
 * @return string HTML string
 */
function Polls_user_display($args)
{
    $dom = ZLanguage::getModuleDomain('Polls');
    $pollid = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'GET');
    $title = FormUtil::getPassedValue('title', isset($args['title']) ? $args['title'] : null, 'GET');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');
    if (!empty($objectid)) {
        $pollid = $objectid;
    }

    // Get the poll
    if (isset($pollid) && is_numeric($pollid)) {
        $item = pnModAPIFunc('Polls', 'user', 'get', array('pollid' => $pollid, 'parse' => true));
    } else {
        $item = pnModAPIFunc('Polls', 'user', 'get', array('title' => $title, 'parse' => true));
        pnQueryStringSetVar('pollid', $item['pollid']);
    }

    if ($item == false) {
        return LogUtil::registerError (__('No such item found.', $dom), 404);
    }

    // Check the user has already voted in this poll
    if (SessionUtil::getVar("poll_voted{$item['pollid']}")) {
		LogUtil::registerStatus(__('You already voted today!', $dom));
		return pnModFunc('Polls', 'user', 'results', $args);
    }

    // Create output object
    $renderer = pnRender::getInstance('Polls');

    // assign the poll
    $renderer->assign($item);

    // Return the output that has been generated by this function
    return $renderer->fetch('polls_user_display.htm');
}

/**
 * display results
 * Display the results of a poll
 * @author Mark West
 * @return string HTML string
 */
function Polls_user_results($args)
{
    $dom = ZLanguage::getModuleDomain('Polls');
    $pollid = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'GET');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');
    $title = FormUtil::getPassedValue('title', isset($args['title']) ? $args['title'] : null, 'GET');
    if (!empty($objectid)) {
        $pollid = $objectid;
    }

    // Get the poll
    if (isset($pollid) && is_numeric($pollid)) {
        $item = pnModAPIFunc('Polls', 'user', 'get', array('pollid' => $pollid, 'parse' => true));
    } else {
        $item = pnModAPIFunc('Polls', 'user', 'get', array('title' => $title, 'parse' => true));
        pnQueryStringSetVar('pollid', $item['pollid']);
    }

    if ($item == false) {
        return LogUtil::registerError (__('No such item found.', $dom), 404);
    }

    // Create output object
    $renderer = pnRender::getInstance('Polls');

    // assign the item
    $renderer->assign($item);
    $renderer->assign('votecount', pnModAPIFunc('Polls', 'user', 'countvotes', array('pollid' => $item['pollid'])));

    // Return the output that has been generated by this function
    return $renderer->fetch('polls_user_results.htm');
}

/**
 * Process vote form
 * Takes the results of the users vote form and calls API function to add vote
 * if vote is allowed
 * @author Mark West
 * @return string HTML string
 */
function Polls_user_vote($args)
{
    $dom = ZLanguage::getModuleDomain('Polls');
    $pollid = FormUtil::getPassedValue('pollid', null, 'POST');
    $title = FormUtil::getPassedValue('title', null, 'POST');
    $displayresults = FormUtil::getPassedValue('displayresults', null, 'POST');
    $voteid = FormUtil::getPassedValue('voteid', null, 'POST');
    $returnurl = FormUtil::getPassedValue('returnurl', null, 'POST');

    // Argument check
    if (!isset($title) ||
        !isset($pollid)) {
        LogUtil::registerArgsError();
        return pnRedirect(pnModURL('Polls', 'user', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::', "$title::", ACCESS_COMMENT)) {
        return LogUtil::registerPermissionError();
    }

    if (SessionUtil::getVar("poll_voted$pollid")) {
        LogUtil::registerError(__('You already voted today!', $dom));
    } else {
        $result = pnModAPIFunc('Polls', 'user', 'vote',
                               array('pollid' => $pollid,
                                     'title' => $title,
                                     'voteid' => $voteid));
    }

    if ($displayresults == 0 && isset($returnurl)) {
        return pnRedirect($returnurl);
    } else {
        return pnModFunc('Polls', 'user', 'results', array('pollid' => $pollid));
    }
}
