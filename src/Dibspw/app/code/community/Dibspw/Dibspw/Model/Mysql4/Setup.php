<?php

/**
 * Dibs A/S
 * Dibs Payment Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Payments & Gateways Extensions
 * @package    Dibspw_Dibspw
 * @author     Dibs A/S
 * @copyright  Copyright (c) 2010 Dibs A/S. (http://www.dibs.dk/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Dibspw_Dibspw_Model_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
        /**
         * delete cache.cfg from Magento Connect directory after/during module install
         * to fix bug with module invisible in module list
         
        $sMageRoot = Mage::getBaseDir();
        if(strpos($sMageRoot, '/') !== FALSE) $sDS = '/';
        else $sDS = '\\';
        $sCacheCfgFile = $sMageRoot . $sDS . 'downloader'. $sDS . 'cache.cfg';
        unlink($sCacheCfgFile);*/
}
?>