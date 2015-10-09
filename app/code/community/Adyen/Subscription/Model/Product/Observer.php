<?php
/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */
 
class Adyen_Subscription_Model_Product_Observer
{
    protected $_saved = false;

    /**
     * @param Varien_Event_Observer $observer
     */
    public function saveSubscriptionProductData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getEvent()->getProduct();

        $subscriptionType = $product->getData('adyen_subscription_type');
        switch ($subscriptionType) {
            case Adyen_Subscription_Model_Product_Subscription::TYPE_ENABLED_ONLY_SUBSCRIPTION:
                $this->_updateProductSubscriptions($product);
                $product->setRequiredOptions(true);
                $product->setHasOptions(true);
                break;
            case Adyen_Subscription_Model_Product_Subscription::TYPE_ENABLED_ALLOW_STANDALONE:
                $this->_updateProductSubscriptions($product);
                $product->setHasOptions(true);
                break;
            default:
                $this->_deleteProductSubscriptions($product);
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @throws Exception
     */
    protected function _updateProductSubscriptions(Mage_Catalog_Model_Product $product)
    {
        $productSubscriptionsData = Mage::app()->getRequest()->getPost('product_subscription');
        $storeId = Mage::app()->getRequest()->getParam('store');

        if (! $productSubscriptionsData) {
            if ($product->getData('adyen_subscription_type') != Adyen_Subscription_Model_Product_Subscription::TYPE_DISABLED) {
                $product->setData('adyen_subscription_type', Adyen_Subscription_Model_Product_Subscription::TYPE_DISABLED);
                Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('adyen_subscription')->__('Adyen Subscription Type is set back to \'Disabled\' because no subscriptions were defined')
                );
            }
            return;
        }

        /** @var array $productSubscriptionIds */
        $productSubscriptionIds = Mage::getModel('adyen_subscription/product_subscription')
            ->getCollection()
            ->addFieldToFilter('product_id', $product->getId())
            ->getAllIds();

        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');

        $i = 1;
        // Save subscriptions
        foreach ($productSubscriptionsData as $id => $subscriptionData) {
            $subscription = Mage::getModel('adyen_subscription/product_subscription')->load($id);

            if (!$subscription->getId()) {
                $subscription->setProductId($product->getId());
            }

            if (!isset($subscriptionData['use_default']) && $storeId) {
                // Save store label
                $labelData = array(
                    'label'         => $subscriptionData['label'],
                    'subscription_id'    => $subscription->getId(),
                    'store_id'      => $storeId,
                );
                $connection->insertOnDuplicate(
                    $resource->getTableName('adyen_subscription/product_subscription_label'),
                    $labelData,
                    array('label')
                );
                unset($subscriptionData['label']);
            }
            if (isset($subscriptionData['use_default']) && $storeId) {
                // Delete store label
                $connection->delete($resource->getTableName('adyen_subscription/product_subscription_label'), array(
                    'subscription_id = ?'    => $subscription->getId(),
                    'store_id = ?'      => $storeId,
                ));
            }

            if ($subscriptionData['customer_group_id'] == '') {
                $subscriptionData['customer_group_id'] = null;
            }
            $subscription->addData($subscriptionData);
            $subscription->setSortOrder($i * 10);

            if (in_array($id, $productSubscriptionIds)) {
                $productSubscriptionIds = array_diff($productSubscriptionIds, array($id));
            }

            $subscription->save();
            $i++;
        }

        // Delete subscriptions
        foreach($productSubscriptionIds as $subscriptionId) {
            Mage::getModel('adyen_subscription/product_subscription')->setId($subscriptionId)->delete();
        }
    }

    protected function _deleteProductSubscriptions(Mage_Catalog_Model_Product $product)
    {
        $ppCollection = Mage::getResourceModel('adyen_subscription/product_subscription_collection')
            ->addProductFilter($product);

        foreach ($ppCollection as $productSubscription) {
            $productSubscription->delete();
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadAttributesAfterCollectionLoad(Varien_Event_Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $productCollection = $observer->getEvent()->getCollection();

        foreach ($productCollection as $product) {
            $this->_loadProductSubscriptionData($product);
        }
        return $this;
    }


    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return $this
     */
    protected function _loadProductSubscriptionData(Mage_Catalog_Model_Product $product)
    {
        if ($product->hasData('adyen_subscription_data')) {
            return $this;
        }
        /** @var Mage_Catalog_Model_Product $product */
        if ($product->getData('adyen_subscription_type') > 0) {
            $subscriptionCollection = Mage::getResourceModel('adyen_subscription/product_subscription_collection')
                ->addProductFilter($product);

            if (! $product->getStore()->isAdmin()) {
                $subscriptionCollection->addStoreFilter($product->getStore());
            }
            $product->setData('adyen_subscription_data', $subscriptionCollection);
        } else {
            $product->setData('adyen_subscription_data', null);
        }
        return $this;
    }


    /**
     * @event catalog_controller_product_view
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addProductTypeSubscriptionHandle(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        /** @noinspection PhpUndefinedMethodInspection */
        $product = Mage::registry('current_product');
        if (! $product) {
            return $this;
        }

        $this->_loadProductSubscriptionData($product);
        if (! $product->getData('adyen_subscription_data')) {
            return $this;
        }
        $subscriptionCollection = $product->getData('adyen_subscription_data');
        if ($subscriptionCollection->count() < 0) {
            return $this;
        }

        /** @var Mage_Core_Model_Layout $layout */
        /** @noinspection PhpUndefinedMethodInspection */
        $layout = $observer->getLayout();
        $layout->getUpdate()->addHandle('PRODUCT_TYPE_adyen_subscription');
        return $this;
    }

    /**
     * Add the selected subscription product subscription to the quote item, if one is selected
     *
     * @event sales_quote_add_item
     * @param Varien_Event_Observer $observer
     * @return $this|void
     */
    public function addSubscriptionProductSubscriptionToQuote(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        /** @noinspection PhpUndefinedMethodInspection */
        $quoteItem = $observer->getQuoteItem();

        /** @var Mage_Catalog_Model_Product $product */
        $product = $quoteItem->getProduct();

        $subscriptionId = $quoteItem->getBuyRequest()->getData('adyen_subscription');
        if (! $subscriptionId) {
            return $this;
        }

        $this->_loadProductSubscriptionData($product);
        if (! $product->getData('adyen_subscription_data')) {
            return $this;
        }

        /** @var Adyen_Subscription_Model_Resource_Product_Subscription_Collection $subscriptionCollection */
        $subscriptionCollection = $product->getData('adyen_subscription_data');
        if ($subscriptionCollection->count() < 0) {
            return $this;
        }

        /** @var Adyen_Subscription_Model_Product_Subscription $subscription */
        $subscription = $subscriptionCollection->getItemById($subscriptionId);

        $option = $quoteItem->getOptionByCode('additional_options');

        if ($subscription) {
            $subscriptionOption = [
                'label'        => Mage::helper('adyen_subscription')->__('Subscription'),
                'code'         => 'adyen_subscription',
                'option_value' => $subscriptionId,
                'value'        => $subscription->getFrontendLabel(),
                'print_value'  => $subscription->getFrontendLabel(),
            ];
        } else {
            $subscriptionOption = [
                'label'        => Mage::helper('adyen_subscription')->__('Subscription'),
                'code'         => 'adyen_subscription',
                'option_value' => 'none',
                'value'        => Mage::helper('adyen_subscription')->__('No subscription'),
                'print_value'  => Mage::helper('adyen_subscription')->__('No subscription'),
            ];
        }

        if ($option == null) {
            $quoteItemOption = Mage::getModel('sales/quote_item_option')->setData([
                'code'       => 'additional_options',
                'product_id' => $quoteItem->getProductId(),
                'value'      => serialize([$subscriptionOption])
            ]);

            $quoteItem->addOption($quoteItemOption);
        } else {
            $additional = unserialize($option->getValue());
            $additional['adyen_subscription'] = $subscriptionOption;
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

        if (! $this->isQuoteAdyenSubscription($quote)) {
            return $this;
        }

        /** @var Mage_Payment_Model_Method_Abstract $methodInstance */
        /** @noinspection PhpUndefinedMethodInspection */
        $methodInstance = $observer->getMethodInstance();
        $methodInstance->setMode('subscription');

        /**
         * The method canCreateContractTypeRecurring returns true for:
         * Inital Payments:   checks for setting in Admin Panel: payment/adyen_abstract/recurringtypes
         *                    if RECURRING or ONECLICK,RECURRING is set
         * Stored cards/sepa: checks recurring_type in BA agreement_data.
         *                    if RECURRING or ONECLICK,RECURRING is set
         *
         * For instances that allow ONECLICK (ONECLICK,RECURRING) we need to set the mode to RECURRING.
         */

        // You need to do a recurring transaction for subscriptions
        if(method_exists($methodInstance, 'setCustomerInteraction')) {
            /** @var $methodInstance Adyen_Payment_Model_Adyen_Oneclick */
            $methodInstance->setCustomerInteraction(false);
        }

        // check if payment method is selected in config
        $selectedSubscriptionPaymentMethods = Mage::helper('adyen_subscription/config')->getSelectedSubscriptionPaymentMethods();

        // check if payment method is in the key
        $code = $methodInstance->getCode();

        // Set method to unavailable and check below if it is possible to use
        $observer->getResult()->isAvailable = false;

        /*
         * Check if payment method is in selectedPaymentMethods and
         * validate if payment method is available for Adyen subscription
         */
        if (array_key_exists($code, $selectedSubscriptionPaymentMethods) &&
            method_exists($methodInstance, 'canCreateAdyenSubscription') &&
            $methodInstance->canCreateAdyenSubscription()) {
                $observer->getResult()->isAvailable = true;
        }

        //@todo move paymen specific logic to Adyen_Payments module, this causes tight coupling.
        // restrict MAESTRO payment method for creditcards because MEASTRO does not support Recurring
        if($code == "adyen_cc") {
            $types = $methodInstance->getAvailableCCTypes();
            if(isset($types['SM'])) {
                unset($types['SM']);
                $methodInstance->setAvailableCCypes($types);
            }
        }

        /*
         * For ONECLCIK payment check if it is allowed by selectedPaymentMethods configuration
         */
        if($code != "adyen_oneclick" && strpos($code, 'adyen_oneclick') !== false)
        {
            $recurringDetails = $methodInstance->getRecurringDetails();

            if(isset($recurringDetails['variant'])) {

                //@todo move the available credit cards to the config, one location where all the credit cards are specified
                $creditcards = array(
                    'visa',
                    'mc',
                    'amex',
                    'discover',
                    'diners',
                    'maestro',
                    'jcb',
                    'elo',
                    'Hipercard'
                );

                if(in_array($recurringDetails['variant'],$creditcards) && isset($selectedSubscriptionPaymentMethods['adyen_cc'])) {
                    $observer->getResult()->isAvailable = true;
                } elseif($recurringDetails['variant'] == "sepadirectdebit" && isset($selectedSubscriptionPaymentMethods['adyen_sepa'])) {
                    $observer->getResult()->isAvailable = true;
                } elseif($recurringDetails['variant'] == "paypal" && isset($selectedSubscriptionPaymentMethods['adyen_hpp_paypal'])) {
                    $observer->getResult()->isAvailable = true;
                } elseif($recurringDetails['variant'] == "directEbanking" && isset($selectedSubscriptionPaymentMethods['adyen_hpp_directEbanking'])) {
                    $observer->getResult()->isAvailable = true;
                }
            }
        }

        if (Mage::app()->getRequest()->getParam('subscription')) {
            if (! method_exists($methodInstance, 'isBillingAgreement') || ! $methodInstance->isBillingAgreement()) {
                $observer->getResult()->isAvailable = false;
            }
        }

        return $this;
    }


    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return mixed|Varien_Object
     */
    public function isQuoteAdyenSubscription(Mage_Sales_Model_Quote $quote)
    {
        if (! $quote->hasData('_is_adyen_subscription')) {
            foreach ($quote->getAllItems() as $quoteItem) {
                /** @var Mage_Sales_Model_Quote_Item $quoteItem */
                $additionalOptions = $quoteItem->getOptionByCode('additional_options');
                if (! $additionalOptions) {
                    continue;
                }

                $options = unserialize($additionalOptions->getValue());

                foreach ($options as $option) {
                    if ($option['code'] == 'adyen_subscription' && $option['option_value'] != 'none') {
                        $quote->setData('_is_adyen_subscription', true);
                        $quoteItem->setData('_adyen_subscription', $option['option_value']);
                        return $quote->getData('_is_adyen_subscription');
                    }
                }
            }

            $quote->setData('_is_adyen_subscription', false);
        }

        return $quote->getData('_is_adyen_subscription');
    }
}