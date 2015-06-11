<?php
/**
 * Ho_Recurring
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category  Ho
 * @package   Ho_Recurring
 * @author    Paul Hachmang – H&O <info@h-o.nl>
 * @copyright 2015 Copyright © H&O (http://www.h-o.nl/)
 * @license   H&O Commercial License (http://www.h-o.nl/license)
 */

// @todo Don't depend on Innoexts_Warehouse module
class Ho_Recurring_Block_Adminhtml_Sales_Order_Create_Form
    extends Innoexts_Warehouse_Block_Adminhtml_Sales_Order_Create_Form {
    /**
     * Retrieve url for loading blocks
     * @return string
     */
    public function getLoadBlockUrl()
    {
        return $this->getUrl('*/*/loadBlock', $this->getRequest()->getParams());
    }
}
