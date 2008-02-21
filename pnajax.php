<?php
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2001, PostNuke Development Team
 * @link http://www.postnuke.com
 * @version $Id: pnajax.php 20166 2006-10-01 21:43:37Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_3rdParty_Modules
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
    $pollid = FormUtil::getPassedValue('pollid', null, 'POST');
    $title = FormUtil::getPassedValue('title', null, 'POST');
    $voteid = FormUtil::getPassedValue('voteid', null, 'POST');

    if (!SecurityUtil::checkPermission('Polls::', "$title::", ACCESS_COMMENT)) {
        AjaxUtil::error(_MODULENOAUTH);
    }

    if (!SecurityUtil::confirmAuthKey()) {
        AjaxUtil::error(_BADAUTHKEY);
    }

    // load the language file
    pnModLangLoad('Polls', 'user');

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

    $pnRender = new pnRender('Polls', false);
    $pnRender->assign($item);
    $pnRender->assign('uservotedalready', $uservotedalready);
    // ajax voting is definately on here...
    $pnRender->assign('ajaxvoting', true);

    // Populate block info and pass to theme
    $result = $pnRender->fetch('polls_block_poll.htm');

    // return the new content for the block
    return array('result' => $result);
}
