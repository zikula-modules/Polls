<?php
/**
 * Polls Module for Zikula
 *
 * @copyright (c) 2008, Mark West
 * @link http://www.markwest.me.uk
 * @version $Id: pnversion.php 19262 2006-06-12 14:45:18Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Polls
*/

$dom = ZLanguage::getModuleDomain('Polls');
$modversion['name'] = 'Polls';
$modversion['displayname'] = __('Polls', $dom);
$modversion['description'] = __('Voting System Module', $dom);
//! module URL must be different to displayname
$modversion['url'] = __('polls', $dom);
$modversion['version'] = '2.1';
$modversion['credits'] = 'pndocs/credits.txt';
$modversion['help'] = 'pndocs/install.txt';
$modversion['changelog'] = 'pndocs/changelog.txt';
$modversion['license'] = 'pndocs/license.txt';
$modversion['official'] = 1;
$modversion['author'] = 'Mark West';
$modversion['contact'] = 'http://www.markwest.me.uk/';
$modversion['securityschema'] = array('Polls::' => 'Poll title::Poll ID');
