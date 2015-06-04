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
 
class Ho_Recurring_Model_Product_Observer
{

    protected $_saved = false;

    /**
     * @param Varien_Event_Observer $observer
     */
    public function saveRecurringProductData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getEvent()->getProduct();

        $recurringType = $product->getData('ho_recurring_type');
        switch ($recurringType) {
            case Ho_Recurring_Model_Product_Profile::TYPE_ENABLED_ONLY_PROFILE:
                $this->_updateProductProfiles($product);
                $product->setRequiredOptions(true);
                $product->setHasOptions(true);
                break;
            case Ho_Recurring_Model_Product_Profile::TYPE_ENABLED_ALLOW_STANDALONE:
                $this->_updateProductProfiles($product);
                $product->setHasOptions(true);
                break;
            default:
                $this->_deleteProductProfiles($product);
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @throws Exception
     */
    protected function _updateProductProfiles(Mage_Catalog_Model_Product $product)
    {
        $productProfilesData = Mage::app()->getRequest()->getPost('product_profile');
        $storeId = Mage::app()->getRequest()->getParam('store');

        /** @var array $productProfileIds */
        $productProfileIds = Mage::getModel('ho_recurring/product_profile')
            ->getCollection()
            ->addFieldToFilter('product_id', $product->getId())
            ->getAllIds();
        if (! $productProfilesData) {
            return;
        }

        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');

        $i = 1;
        // Save profiles
        foreach ($productProfilesData as $id => $profileData) {
            $profile = Mage::getModel('ho_recurring/product_profile')->load($id);

            if (!$profile->getId()) {
                $profile->setProductId($product->getId());
            }

            if (!isset($profileData['use_default']) && $storeId) {
                // Save store label
                $labelData = array(
                    'label'         => $profileData['label'],
                    'profile_id'    => $profile->getId(),
                    'store_id'      => $storeId,
                );
                $connection->insertOnDuplicate(
                    $resource->getTableName('ho_recurring/product_profile_label'),
                    $labelData,
                    array('label')
                );
                unset($profileData['label']);
            }
            if (isset($profileData['use_default']) && $storeId) {
                // Delete store label
                $connection->delete($resource->getTableName('ho_recurring/product_profile_label'), array(
                    'profile_id = ?'    => $profile->getId(),
                    'store_id = ?'      => $storeId,
                ));
            }

            $profile->addData($profileData);
            $profile->setSortOrder($i * 10);

            if (in_array($id, $productProfileIds)) {
                $productProfileIds = array_diff($productProfileIds, array($id));
            }

            $profile->save();
            $i++;
        }

        // Delete profiles
        foreach($productProfileIds as $profileId) {
            Mage::getModel('ho_recurring/product_profile')->setId($profileId)->delete();
        }
    }

    protected function _deleteProductProfiles(Mage_Catalog_Model_Product $product)
    {
        $ppCollection = Mage::getResourceModel('ho_recurring/product_profile_collection')
            ->addProductFilter($product);

        foreach ($ppCollection as $productProfile) {
            $productProfile->delete();
        }

        return $this;
    }

    /**
     * Process `giftcard_amounts` attribute afterLoad logic on loading by collection
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadAttributesAfterCollectionLoad(Varien_Event_Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $productCollection = $observer->getEvent()->getCollection();

        foreach ($productCollection as $product) {
            $this->_loadProductRecurringData($product);
        }
        return $this;
    }


    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return $this
     */
    protected function _loadProductRecurringData(Mage_Catalog_Model_Product $product)
    {
        if ($product->hasData('ho_recurring_data')) {
            return $this;
        }
        /** @var Mage_Catalog_Model_Product $product */
        if ($product->getData('ho_recurring_type') > 0) {
            $profileCollection = Mage::getResourceModel('ho_recurring/product_profile_collection')
                ->addProductFilter($product);

            if (! $product->getStore()->isAdmin()) {
                $profileCollection->addStoreFilter($product->getStore());
            }
            $product->setData('ho_recurring_data', $profileCollection);
        } else {
            $product->setData('ho_recurring_data', null);
        }
        return $this;
    }

    /**
     * Initialize product options renderer with giftcard specific params
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function initOptionRenderer(Varien_Event_Observer $observer)
    {
        Ho_Recurring_Exception::throwException('Not yet implemented');


//        $block = $observer->getBlock();
//        $block->addOptionsRenderCfg('giftcard', 'enterprise_giftcard/catalog_product_configuration');
        return $this;
    }


    /**
     * @event catalog_controller_product_view
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addProductTypeRecurringHandle(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        /** @noinspection PhpUndefinedMethodInspection */
        $product = Mage::registry('current_product');
        if (! $product) {
            return $this;
        }

        $this->_loadProductRecurringData($product);
        if (! $product->getData('ho_recurring_data')) {
            return $this;
        }
        $recurringCollection = $product->getData('ho_recurring_data');
        if ($recurringCollection->count() < 0) {
            return $this;
        }

        /** @var Mage_Core_Model_Layout $layout */
        /** @noinspection PhpUndefinedMethodInspection */
        $layout = $observer->getLayout();
        $layout->getUpdate()->addHandle('PRODUCT_TYPE_ho_recurring');
        return $this;
    }

    /**
     * Add the selected recurring product profile to the quote item, if one is selected
     *
     * @event sales_quote_add_item
     * @param Varien_Event_Observer $observer
     * @return $this|void
     */
    public function addRecurringProductProfileToQuote(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        /** @noinspection PhpUndefinedMethodInspection */
        $quoteItem = $observer->getQuoteItem();

        /** @var Mage_Catalog_Model_Product $product */
        $product = $quoteItem->getProduct();

        $profileId = $quoteItem->getBuyRequest()->getData('ho_recurring_profile');
        if (! $profileId) {
            return $this;
        }

        $this->_loadProductRecurringData($product);
        if (! $product->getData('ho_recurring_data')) {
            return $this;
        }

        /** @var Ho_Recurring_Model_Resource_Product_Profile_Collection $recurringCollection */
        $recurringCollection = $product->getData('ho_recurring_data');
        if ($recurringCollection->count() < 0) {
            return $this;
        }

        /** @var Ho_Recurring_Model_Product_Profile $profile */
        $profile = $recurringCollection->getItemById($profileId);

        $option = $quoteItem->getOptionByCode('additional_options');

        if ($profile) {
            $profileOption = [
                'label'        => 'Subscription',
                'code'         => 'ho_recurring_profile',
                'option_value' => $profileId,
                'value'        => $profile->getFrontendLabel(),
                'print_value'  => $profile->getFrontendLabel(),
            ];
        } else {
            $profileOption = [
                'label'        => 'Subscription',
                'code'         => 'ho_recurring_profile',
                'option_value' => 'none',
                'value'        => 'No subscription',
                'print_value'  => 'No subscription',
            ];
        }

        if ($option == null) {
            $quoteItemOption = Mage::getModel('sales/quote_item_option')->setData([
                'code'       => 'additional_options',
                'product_id' => $quoteItem->getProductId(),
                'value'      => serialize([$profileOption])
            ]);

            $quoteItem->addOption($quoteItemOption);
        } else {
            $additional = unserialize($option->getValue());
            $additional['ho_recurring_profile'] = $profileOption;
            $option->setValue(serialize($additional));
        }
    }


    /**
     * @event payment_method_is_active
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function isPaymentMethodActive(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        /** @noinspection PhpUndefinedMethodInspection */
        $quote = $observer->getQuote();
        if (! $quote) {
            return $this;
        }

        if (! $this->_isQuoteHoRecurring($quote)) {
            return $this;
        }

        /** @var Mage_Payment_Model_Method_Abstract $methodInstance */
        /** @noinspection PhpUndefinedMethodInspection */
        $methodInstance = $observer->getMethodInstance();
        if (! $methodInstance->canCreateBillingAgreement()) {
            $observer->getResult()->isAvailable = false;
            return $this;
        }

        return $this;
    }


    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return mixed|Varien_Object
     */
    protected function _isQuoteHoRecurring(Mage_Sales_Model_Quote $quote)
    {
        if (! $quote->hasData('_is_ho_recurring')) {
            foreach ($quote->getAllItems() as $quoteItem) {
                /** @var Mage_Sales_Model_Quote_Item $quoteItem */
                $additionalOptions = $quoteItem->getOptionByCode('additional_options');
                if (! $additionalOptions) {
                    continue;
                }

                $options = unserialize($additionalOptions->getValue());

                foreach ($options as $option) {
                    if ($option['code'] == 'ho_recurring_profile' && $option['option_value'] != 'none') {
                        $quote->setData('_is_ho_recurring', true);
                        return $quote->getData('_is_ho_recurring');
                    }
                }
            }

            return $quote->setData('_is_ho_recurring', false);
        }

        return $quote->getData('_is_ho_recurring');
    }
}