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

class Polls_Block_Poll extends Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        // Security
        SecurityUtil::registerPermissionSchema('Polls:Pollblock:', 'Block title::');
    }
    
    /**
     * get information on block
     */
    public function info()
    {
        // Values
        return array('module'          => 'Polls',
                     'text_type'       => $this->__('Polls'),
                     'text_type_long'  => $this->__('Show a poll'),
                     'allow_multiple'  => true,
                     'form_content'    => false,
                     'form_refresh'    => false,
                     'show_preview'    => true,
                     'admin_tableless' => true);
    }
    
    /**
     * display block
     */
    public function display($blockinfo)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Polls:Pollblock:', "$blockinfo[title]::", ACCESS_READ)) {
            return;
        }

        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        
        // check if a poll id has been defined
        if (empty($vars['pollid'])) {
            return;
        }

        // Check if the user is allowed to vote (meaning he has already voted in this poll)
        $allowedtovote = (bool)ModUtil::apiFunc('Polls', 'user', 'allowedtovote', array('pollid' => $vars['pollid']));

        // Define the cache id
        $this->view->cache_id = UserUtil::getVar('uid') . $vars['pollid'] . $allowedtovote;
        
        $template = 'polls_block_poll.tpl';
        
        // check out if the contents are cached.
        if ($this->view->is_cached($template)) {
            // Populate block info and pass to theme
            $blockinfo['content'] = $this->view->fetch($template);
            return BlockUtil::themeBlock($blockinfo);
        }

        // Get the poll
        $item = ModUtil::apiFunc('Polls', 'user', 'get',
                             array('pollid' => $vars['pollid']));

        // check if there's an item to show
        if ($item == false) {
            return;
        }

        $this->view->assign('vars', $vars);
        $this->view->assign('item', $item);
        $this->view->assign('allowedtovote', $allowedtovote);
        
        if (SecurityUtil::checkPermission('Polls::', "$item[title]::", ACCESS_COMMENT)) {
            $this->view->assign('usercanvote', true);
        } else {
            $this->view->assign('usercanvote', false);
        }

        // Populate block info and pass to theme
        $blockinfo['content'] = $this->view->fetch($template);

        return BlockUtil::themeBlock($blockinfo);
    }
    
    /**
     * modify block settings
     */
    public function modify($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Defaults
        if (empty($vars['pollid'])) {
            $vars['pollid'] = -1; // latest
        }
        if (empty($vars['ajaxvoting'])) {
            $vars['ajaxvoting'] = false;
        }

        // Get all polls
        $items = ModUtil::apiFunc('Polls', 'user', 'getall');

        // form a list of polls suitable for html_options
        $polloptions = array(-1 => $this->__('Latest poll'));

        foreach ($items as $item) {
            $polloptions[$item['pollid']] = $item['title'];
        }

        // disable caching
        $this->caching = false;
        
        // assign data
        $this->view->assign('polls', $polloptions);
        $this->view->assign('vars', $vars);

        // Return output
        return $this->view->fetch('polls_block_poll_modify.tpl');
    }
    
    /**
     * update block settings
     */
    public function update($blockinfo)
    {
        $vars['pollid']     = (int)FormUtil::getPassedValue('pollid', 0, 'POST');
        $vars['ajaxvoting'] = (boolean)FormUtil::getPassedValue('ajaxvoting', false, 'POST');

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        return $blockinfo;
    }
}

