<?php
/**
 * Polls Module for Zikula
 *
 * @copyright (c) 2010, Mark West
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*/

$dom = ZLanguage::getModuleDomain('Polls');
$modversion['name']             = 'Polls';
$modversion['displayname']      = __('Polls', $dom);
$modversion['description']      = __('Voting System Module', $dom);
$modversion['url']              = __('polls', $dom);
$modversion['version']          = '2.1.0';
$modversion['credits']          = 'pndocs/credits.txt';
$modversion['help']             = 'pndocs/install.txt';
$modversion['changelog']        = 'pndocs/changelog.txt';
$modversion['license']          = 'pndocs/license.txt';
$modversion['official']         = 1;
$modversion['author']           = 'Mark West, Carsten Volmer';
$modversion['contact']          = 'http://code.zikula.org/advancedpolls';
$modversion['securityschema']   = array('Polls::' => 'Poll title::Poll ID');
