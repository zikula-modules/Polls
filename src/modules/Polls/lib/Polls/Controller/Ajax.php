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
 
class Polls_Controller_Ajax extends Zikula_Controller
{
    /**
     * Log a vote and display the results form
     *
     * @param pollid the poll to vote on
     * @param voteid the option to vote on
     * @return string updated display for the block
     */
    public function vote()
    {
        $pollid = FormUtil::getPassedValue('pollid', null, 'POST');
        $title  = FormUtil::getPassedValue('title', null, 'POST');
        $voteid = FormUtil::getPassedValue('voteid', null, 'POST');

        if (!SecurityUtil::checkPermission('Polls::', "$title::", ACCESS_COMMENT)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        if (!SecurityUtil::confirmAuthKey()) {
            //LogUtil::registerAuthidError();
            //throw new Zikula_Exception_Fatal();
        }

        // Check if the user is allowed to vote (meaning he has already voted in this poll)
        $allowedtovote = (bool)ModUtil::apiFunc('Polls', 'user', 'allowedtovote', array('pollid' => $pollid));
        
        if ($allowedtovote) {
            $result = ModUtil::apiFunc('Polls', 'user', 'vote',
                                   array('pollid' => $pollid,
                                         'title' => $title,
                                         'voteid' => $voteid));
        }

        // Get the poll
        $item = ModUtil::apiFunc('Polls', 'user', 'get', array('pollid' => $pollid));

        // Check the user has is NOW allowed to vote in this poll
        $allowedtovote = (bool)ModUtil::apiFunc('Polls', 'user', 'allowedtovote', array('pollid' => $pollid));

        $this->view->caching = false;
        
        $this->view->assign('item', $item);
        $this->view->assign('allowedtovote', $allowedtovote);
        
        // ajax voting is definately on here...
        $vars['ajaxvoting'] = true;
        $this->view->assign('vars', $vars);
        
        // can user vote?
        if (SecurityUtil::checkPermission('Polls::', "$item[title]::", ACCESS_COMMENT)) {
            $this->view->assign('usercanvote', true);
        } else {
            $this->view->assign('usercanvote', false);
        }

        // Populate block info and pass to theme
        $result = $this->view->fetch('polls_block_poll.tpl');

        // return the new content for the block
        return new Zikula_Response_Ajax(array('result' => $result));
    }
}


