<?php
/**
 * Polls Module for Zikula
 *
 * @copyright (c) 2008, Mark West
 * @link http://www.markwest.me.uk
 * @version $Id: pnajax.php 20166 2006-10-01 21:43:37Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Polls
*/

/**
 * Log a vote and display the results form
 *
 * @author Mark West
 * @param pollid the poll to vote on
 * @param voteid the option to vote on
 * @return string updated display for the block
 */
function polls_ajax_vote()
{
    $dom = ZLanguage::getModuleDomain('Polls');
    $pollid = FormUtil::getPassedValue('pollid', null, 'POST');
    $title  = FormUtil::getPassedValue('title', null, 'POST');
    $voteid = FormUtil::getPassedValue('voteid', null, 'POST');

    if (!SecurityUtil::checkPermission('Polls::', "$title::", ACCESS_COMMENT)) {
        AjaxUtil::error(__('Sorry! No authorization to access this module.', $dom));
    }

    if (!SecurityUtil::confirmAuthKey()) {
        AjaxUtil::error(__("Invalid 'authkey':  this probably means that you pressed the 'Back' button, or that the page 'authkey' expired. Please refresh the page and try again.", $dom));
    }

    // Check the user has already voted in this poll
    $uservotedalready = (bool)SessionUtil::getVar("poll_voted{$pollid}");

    if (!$uservotedalready) {
        $result = pnModAPIFunc('Polls', 'user', 'vote',
                               array('pollid' => $pollid,
                                     'title' => $title,
                                     'voteid' => $voteid));
    }

    // Get the poll
    $item = pnModAPIFunc('Polls', 'user', 'get', array('pollid' => $pollid));

    // Check the user has now voted in this poll
    $uservotedalready = (bool)SessionUtil::getVar("poll_voted{$pollid}");

    $renderer = new pnRender('Polls', false);
    $renderer->assign($item);
    $renderer->assign('uservotedalready', $uservotedalready);
    // ajax voting is definately on here...
    $renderer->assign('ajaxvoting', true);

    // Populate block info and pass to theme
    $result = $renderer->fetch('polls_block_poll.htm');

    // return the new content for the block
    return array('result' => $result);
}
