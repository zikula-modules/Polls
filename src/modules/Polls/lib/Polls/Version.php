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
        
        return $meta;
    }
}
