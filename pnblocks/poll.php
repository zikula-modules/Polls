<?php
/**
 * Polls Module for Zikula
 *
 * @copyright (c) 2008, Mark West
 * @link http://www.markwest.me.uk
 * @version $Id: poll.php 20108 2006-09-24 19:56:21Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Polls
 */

/**
 * initialise block
 */
function Polls_pollblock_init()
{
    // Security
    pnSecAddSchema('Polls:Pollblock:', 'Block title::');
}

/**
 * get information on block
 */
function Polls_pollblock_info()
{
    // Values
    return array('text_type' => 'Polls',
                 'module' => 'Polls',
                 'text_type_long' => 'Show a Poll',
                 'allow_multiple' => true,
                 'form_content' => false,
                 'form_refresh' => false,
                 'show_preview' => true,
                 'admin_tableless' => true);
}

/**
 * display block
 */
function Polls_pollblock_display($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('Polls:Pollblock:', "$blockinfo[title]::", ACCESS_READ)) {
        return;
    }

    // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // check if a poll id has been defined
    if (empty($vars['pollid'])) {
        return;
    }

    // debug line
	//SessionUtil::delVar("poll_voted{$vars['pollid']}");
    // Check the user has already voted in this poll
    $uservotedalready = SessionUtil::getVar("poll_voted{$vars['pollid']}");

    // Create output object
    $renderer = pnRender::getInstance('Polls');

    // Define the cache id
    $renderer->cache_id = pnUserGetVar('uid') . $vars['pollid'] . $uservotedalready;

    // check out if the contents are cached.
    if ($renderer->is_cached('polls_block_poll.htm')) {
        // Populate block info and pass to theme
        $blockinfo['content'] = $renderer->fetch('polls_block_poll.htm');
        return themesideblock($blockinfo);
    }

    // Get the poll
    $item = pnModAPIFunc('Polls', 'user', 'get', array('pollid' => $vars['pollid']));

    $renderer->assign($item);
    $renderer->assign($vars);
    $renderer->assign('uservotedalready', $uservotedalready);

    // Populate block info and pass to theme
    $blockinfo['content'] = $renderer->fetch('polls_block_poll.htm');

    return themesideblock($blockinfo);
}

/**
 * modify block settings
 */
function Polls_Pollblock_modify($blockinfo)
{
    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Defaults
    if (empty($vars['pollid'])) {
        $vars['pollid'] = 1;
    }
    if (empty($vars['ajaxvoting'])) {
        $vars['ajaxvoting'] = false;
    }

    // Get all polls
    $items = pnModAPIFunc('Polls', 'user', 'getall');

    // form a list of polls suitable for html_options
    $polloptions = array();
    foreach ($items as $item) {
        $polloptions[$item['pollid']] = $item['title'];
    }

    // Create output object
    $renderer = pnRender::getInstance('Polls', false);

    // assign data
    $renderer->assign('polls', $polloptions);
    $renderer->assign($vars);

    // Return output
    return $renderer->fetch('polls_block_poll_modify.htm');
}

/**
 * update block settings
 */
function Polls_Pollblock_update($blockinfo)
{
    $vars['pollid'] = FormUtil::getPassedValue('pollid', null, 'POST');
    $vars['ajaxvoting'] = FormUtil::getPassedValue('ajaxvoting', false, 'POST');

    $blockinfo['content'] = pnBlockVarsToContent($vars);

    return $blockinfo;
}
