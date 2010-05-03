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
 * the main administration function
 *
 * @author Mark West
 * @return string HTML string
 */
function Polls_admin_main()
{
    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $renderer = pnRender::getInstance('Polls', false);

    // Return the output that has been generated by this function
    return $renderer->fetch('polls_admin_main.htm');
}

/**
 * add new item
 *
 * @author Mark West
 * @return string HTML string
 */
function Polls_admin_new()
{
    $dom = ZLanguage::getModuleDomain('Polls');
    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::', '::', ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    // Get the module configuration vars
    $modvars = pnModGetVar('Polls');

    // Create output object
    $renderer = pnRender::getInstance('Polls', false);

    if ($modvars['enablecategorization']) {
        // load the category registry util
        if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
            pn_exit (__f('Error! Unable to load class [%s]', 'CategoryRegistryUtil', $dom));
        }
        $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories ('Polls', 'poll_desc');

        $renderer->assign('catregistry', $catregistry);
    }

    $renderer->assign($modvars);
    $renderer->assign('lang', ZLanguage::getLanguageCode());

    // Return the output that has been generated by this function
    return $renderer->fetch('polls_admin_new.htm');
}

/**
 * create item
 *
 * @param 'name' the name of the item to be created
 * @param 'number' the number of the item to be created
 * @author Mark West
 * @return bool true
 */
function Polls_admin_create($args)
{
    $dom = ZLanguage::getModuleDomain('Polls');
    $poll = FormUtil::getPassedValue('poll', isset($args['poll']) ? $args['poll'] : null, 'POST');

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError (pnModURL('Polls', 'admin', 'view'));
    }

    // Notable by its absence there is no security check here

    // Create the poll
    $pollid = pnModAPIFunc('Polls', 'admin', 'create', $poll);

    if ($pollid != false) {
        // Success
        LogUtil::registerStatus (__('Done! Item created.', $dom));
    }

    return pnRedirect(pnModURL('Polls', 'admin', 'view'));
}

/**
 * modify an item
 *
 * @param 'pollid' the id of the item to be modified
 * @author Mark West
 * @return string HTML string
 */
function Polls_admin_modify($args)
{
    $dom = ZLanguage::getModuleDomain('Polls');
    $pollid = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'GET');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');

    if (!empty($objectid)) {
        $pollid = $objectid;
    }

    // Validate the essential parameters
    if (empty($pollid)) {
        return LogUtil::registerArgsError();
    }

    // Get the poll
    $item = pnModAPIFunc('Polls', 'user', 'get', array('pollid' => $pollid));

    if ($item == false) {
        return LogUtil::registerError(__('Error! No such poll found %s', $dom), 404);
    }

    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::', "$item[title]::$pollid", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Get the module configuration vars
    $modvars = pnModGetVar('Polls');

    // Create output object
    $renderer = pnRender::getInstance('Polls', false);

    // load the categories system
    if ($modvars['enablecategorization']) {
        // load the category registry util
        if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
            pn_exit (__f('Error! Unable to load class [%s]', 'CategoryRegistryUtil', $dom));
        }
        $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories ('Polls', 'poll_desc');

        $renderer->assign('catregistry', $catregistry);
    }

    // Assign the item
    $renderer->assign($item);
    $renderer->assign($modvars);

    // Return the output that has been generated by this function
    return $renderer->fetch('polls_admin_modify.htm');
}

/**
 * update item
 *
 * @param 'pollid' the id of the item to be updated
 * @param 'polltitle' the name of the item to be updated
 * @param 'polllanguage' the language of the item to be updated
 * @author Mark West
 * @return bool true
 */
function Polls_admin_update($args)
{
    $dom = ZLanguage::getModuleDomain('Polls');
    $poll = FormUtil::getPassedValue('poll', isset($args['poll']) ? $args['poll'] : null, 'POST');
    if (!empty($poll['objectid'])) {
        $poll['pollid'] = $poll['objectid'];
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError (pnModURL('Polls', 'admin', 'view'));
    }

    // Notable by its absence there is no security check here
    // Update the poll
    if (pnModAPIFunc('Polls', 'admin', 'update', $poll)) {
        // Success
        LogUtil::registerStatus (__('Done! Item updated.', $dom));
    }

    return pnRedirect(pnModURL('Polls', 'admin', 'view'));
}

/**
 * delete item
 *
 * @param 'pollid' the id of the item to be deleted
 * @param 'confirmation' confirmation that this item can be deleted
 * @author Mark West
 * @return mixed HTML string if no confirmation, true otherwise
 */
function Polls_admin_delete($args)
{
    $dom = ZLanguage::getModuleDomain('Polls');
    $pollid = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'REQUEST');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
    $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
    if (!empty($objectid)) {
        $pollid = $objectid;
    }

    // Get the poll
    $item = pnModAPIFunc('Polls', 'user', 'get', array('pollid' => $pollid));

    if ($item == false) {
        return LogUtil::registerError (__('Error! No such item found.', $dom), 404);
    }

    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::Item', "$item[title]::$pollid", ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    // Check for confirmation.
    if (empty($confirmation)) {
        // No confirmation yet
        // Create output object
        $renderer = pnRender::getInstance('Polls', false);

        // Add a hidden variable for the item id
        $renderer->assign('pollid', $pollid);

        // Return the output that has been generated by this function
        return $renderer->fetch('polls_admin_delete.htm');
    }

    // If we get here it means that the user has confirmed the action

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError (pnModURL('Polls', 'admin', 'view'));
    }

    // Delete the poll
    if (pnModAPIFunc('Polls', 'admin', 'delete', array('pollid' => $pollid))) {
        // Success
        LogUtil::registerStatus (__('Done! Item deleted.', $dom));
    }

    return pnRedirect(pnModURL('Polls', 'admin', 'view'));
}

/**
 * view items
 *
 * @author Mark West
 * @return string HTML string
 */
function Polls_admin_view()
{
    $dom = ZLanguage::getModuleDomain('Polls');
    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Get parameters from whatever input we need.
    $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');
    $property = FormUtil::getPassedValue('polls_property', isset($args['polls_property']) ? $args['polls_property'] : null, 'POST');
    $category = FormUtil::getPassedValue("polls_{$property}_category", isset($args["polls_{$property}_category"]) ? $args["polls_{$property}_category"] : null, 'POST');
    $clear    = FormUtil::getPassedValue('clear', false, 'POST');
    if ($clear) {
        $property = null;
        $category = null;
    }

    // get all module vars for later use
    $modvars = pnModGetVar('Polls');

    if ($modvars['enablecategorization']) {
        // load the category registry util
        if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
            pn_exit (__f('Error! Unable to load class [%s]', 'CategoryRegistryUtil', $dom));
        }
        $catregistry  = CategoryRegistryUtil::getRegisteredModuleCategories('Polls', 'poll_desc');
        $properties = array_keys($catregistry);

        // Validate and build the category filter - mateo
        if (!empty($property) && in_array($property, $properties) && !empty($category)) {
            $catFilter = array($property => $category);
        }

        // Assign a default property - mateo
        if (empty($property) || !in_array($property, $properties)) {
            $property = $properties[0];
        }

        // plan ahead for ML features
        $propArray = array();
        foreach ($properties as $prop) {
            $propArray[$prop] = $prop;
        }
    }

    // Get all the polls
    $items = pnModAPIFunc('Polls', 'user', 'getall',
                          array('startnum' => $startnum,
                                'numitems' => $modvars['itemsperpage'],
                                'ignoreml' => true,
                                'category' => isset($catFilter) ? $catFilter : null,
                                'catregistry' => isset($catregistry) ? $catregistry : null));

    if (!$items)
        $items = array();

    $polls = array();
    foreach ($items as $item) {
        $options = array();
         if (SecurityUtil::checkPermission( 'Polls::', "$item[title]::$item[pollid]", ACCESS_EDIT)) {
            $options[] = array('url' => pnModURL('Polls', 'admin', 'modify', array('pollid' => $item['pollid'])),
                               'image' => 'xedit.gif',
                               'title' => __('Edit', $dom));
            if (SecurityUtil::checkPermission( 'Polls::', "$item[title]::$item[pollid]", ACCESS_DELETE)) {
                $options[] = array('url' => pnModURL('Polls', 'admin', 'delete', array('pollid' => $item['pollid'])),
                                   'image' => '14_layer_deletelayer.gif',
                                   'title' => __('Delete', $dom));
            }
        }

        // Add the calculated menu options to the item array
        $item['options'] = $options;
        $polls[] = $item;
    }

    // Create output object
    $renderer = pnRender::getInstance('Polls', false);

    // Assign the items to the template
    $renderer->assign('polls', $polls);
    $renderer->assign($modvars);

    // Assign the default language
    $renderer->assign('lang', ZLanguage::getLanguageCode());

    // Assign the categories information if enabled
    if ($modvars['enablecategorization']) {
        $renderer->assign('catregistry', $catregistry);
        $renderer->assign('numproperties', count($propArray));
        $renderer->assign('properties', $propArray);
        $renderer->assign('property', $property);
        $renderer->assign("category", $category);
    }

    // Assign the information required to create the pager
    $renderer->assign('pager', array('numitems'     => pnModAPIFunc('Polls', 'user', 'countitems', array('category' => isset($catFilter) ? $catFilter : null)),
                                     'itemsperpage' => $modvars['itemsperpage']));

    // Return the output that has been generated by this function
    return $renderer->fetch('polls_admin_view.htm');
}

/**
 * modify module configuration
 *
 * @author Mark West
 * @return string HTML string
 */
function Polls_admin_modifyconfig()
{
    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $renderer = pnRender::getInstance('Polls', false);

    // Assign the module vars
    $renderer->assign(pnModGetVar('Polls'));

    // Return the output that has been generated by this function
    return $renderer->fetch('polls_admin_modifyconfig.htm');
}

/**
 * update module configuration
 *
 * @author Mark West
 * @param int $itemsperpage items per page
 * @param int $scale scaling factor for results bar
 * @return string HTML string
 */
function Polls_admin_updateconfig()
{
    $dom = ZLanguage::getModuleDomain('Polls');
    // Security check
    if (!SecurityUtil::checkPermission( 'Polls::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError (pnModURL('Polls', 'admin', 'view'));
    }

    // Update module variables
    $itemsperpage = FormUtil::getPassedValue('itemsperpage', 10, 'POST');
    pnModSetVar('Polls', 'itemsperpage', $itemsperpage);

    $scale = FormUtil::getPassedValue('scale', 1, 'POST');
    pnModSetVar('Polls', 'scale', $scale);

    $sortorder = FormUtil::getPassedValue('sortorder', 0, 'POST');
    pnModSetVar('Polls', 'sortorder', $sortorder);

    $scale = FormUtil::getPassedValue('enablecategorization', 1, 'POST');
    pnModSetVar('Polls', 'enablecategorization', $enablecategorization);

    $addcategorytitletopermalink = (bool)FormUtil::getPassedValue('addcategorytitletopermalink', false, 'POST');
    pnModSetVar('Polls', 'addcategorytitletopermalink', $addcategorytitletopermalink);

    // Let any other modules know that the modules configuration has been updated
    pnModCallHooks('module','updateconfig','Polls', array('module' => 'Polls'));

    // the module configuration has been updated successfuly
    LogUtil::registerStatus (__('Done! Module configuration updated.', $dom));

    return pnRedirect(pnModURL('Polls', 'admin', 'view'));
}
