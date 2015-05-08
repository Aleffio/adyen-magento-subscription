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
 * @category    Ho
 * @package     Ho_Recurring
 * @copyright   Copyright © 2015 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Maikel Koek – H&O <info@h-o.nl>
 */

class Ho_Recurring_Model_Catalog_Product_Observer extends Mage_Core_Model_Abstract
{
    protected $_saved = false;

    /**
     * @param Varien_Event_Observer $observer
     */
    public function saveRecurringProductData(Varien_Event_Observer $observer)
    {
        if ($this->_saved) {
            return;
        }

        $this->_saved = true;

        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getEvent()->getProduct();

        try {
            $productProfiles = $this->_getRequest()->getPost('product_profile');

            foreach ($productProfiles as $id => $profileData) {
                $profile = Mage::getModel('ho_recurring/product_profile')->load($id);

                if (!$profile->getId()) {
                    echo 'doesnt exist yet<br/>';
                    $profile->setProductId($product->getId());
                }

                $profile->addData($profileData);

                $profile->save();
            }
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ho_recurring')->__(
                    'Something went wrong when trying to save the recurring profile data: %s',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @return Mage_Core_Controller_Request_Http
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
}
