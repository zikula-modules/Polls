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
 
class Polls_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['name']           = 'Polls';
        $meta['displayname']    = $this->__('Polls');
        $meta['description']    = $this->__('Voting System Module');
        $meta['url']            = 'polls';
        $meta['version']        = '3.0.0';
        $meta['securityschema'] = array('Polls::' => 'Poll title::Poll ID');
        $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));
        
        return $meta;
    }
	
    protected function setupHookBundles()
    {
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.polls.ui_hooks.p', 'ui_hooks', __('Polls Display Hooks'));
        $bundle->addEvent('display_view', 'polls.ui_hooks.p.display_view');
        $bundle->addEvent('form_edit', 'polls.ui_hooks.p.form_edit');
        $bundle->addEvent('form_delete', 'polls.ui_hooks.p.form_delete');
        $bundle->addEvent('validate_edit', 'polls.ui_hooks.p.validate_edit');
        $bundle->addEvent('validate_delete', 'polls.ui_hooks.p.validate_delete');
        $bundle->addEvent('process_edit', 'polls.ui_hooks.p.process_edit');
        $bundle->addEvent('process_delete', 'polls.ui_hooks.p.process_delete');
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.polls.filter_hooks.p', 'filter_hooks', __('Polls Filter Hooks'));
        $bundle->addEvent('filter', 'polls.filter_hooks.p.filter');
        $this->registerHookSubscriberBundle($bundle);
    }
}
