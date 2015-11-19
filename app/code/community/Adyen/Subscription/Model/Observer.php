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

class Adyen_Subscription_Model_Observer extends Mage_Core_Model_Abstract
{

    /**
     * @event salesrule_rule_condition_combine
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addFiltersToSalesRuleCombine(Varien_Event_Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Varien_Object $additional */
        $additional = $observer->getAdditional();

        /** @noinspection PhpUndefinedMethodInspection */
        $conditions = $additional->getConditions() ?: array();

        $conditions = array_merge_recursive(array(array(
            'label' => Mage::helper('adyen_subscription')->__('Adyen Subscription'),
            'value' => array(
                array(
                    'label' => Mage::helper('adyen_subscription')->__('Product Subscription ID'),
                    'value' => 'adyen_subscription/salesRule_condition_productSubscription'
                ), array(
                    'label' => Mage::helper('adyen_subscription')->__('Number of subscription order'),
                    'value' => 'adyen_subscription/salesRule_condition_quoteCount'
                )
            )
        )), $conditions);

        /** @noinspection PhpUndefinedMethodInspection */
        $additional->setConditions($conditions);
        return $this;
    }


    /**
     * @event controller_action_layout_load_before
     * @param Varien_Event_Observer $observer
     */
    public function addAdminhtmlSalesOrderCreateHandles(Varien_Event_Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        if (! $observer->getAction() instanceof Mage_Adminhtml_Sales_Order_CreateController) {
            return;
        }

        $subscriptionId = Mage::app()->getRequest()->getParam('subscription');
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (! $subscription->getId()) {
            return;
        }

        Mage::register('current_subscription', $subscription);
        Mage::app()->getLayout()->getUpdate()->addHandle('adyen_subscription_active_quote_edit');
    }

    /**
     * Save additional (subscription) product options (added in addSubscriptionProductSubscriptionToQuote)
     * from quote items to order items
     *
     * @event sales_convert_quote_item_to_order_item
     * @param Varien_Event_Observer $observer
     */
    public function addSubscriptionProductSubscriptionToOrder(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        /** @noinspection PhpUndefinedMethodInspection */
        $quoteItem = $observer->getItem();
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        /** @noinspection PhpUndefinedMethodInspection */
        $orderItem = $observer->getOrderItem();

        if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
            $options = $orderItem->getProductOptions();

            $options['additional_options'] = unserialize($additionalOptions->getValue());
            $orderItem->setProductOptions($options);
        }
    }

    /**
     * Adds virtual grid column to order grid records generation
     * @param Varien_Event_Observer $observer
     */
    public function addColumnToResource(Varien_Event_Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /* @var $resource Mage_Sales_Model_Mysql4_Order */
        $resource = $observer->getEvent()->getResource();
        $resource->addVirtualGridColumn(
            'created_subscription_id',
            'adyen_subscription/subscription',
            array('entity_id' => 'order_id'),
            'entity_id'
        );
    }

    /**
     * Set the right amount of qty on the order items when placing an order.
     * The ordered qty is multiplied by the 'qty in subscription' amount of the
     * selected subscription.
     *
     * @event sales_order_place_before
     * @param Varien_Event_Observer $observer
     */
    public function calculateItemQty(Varien_Event_Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();

        foreach ($order->getItemsCollection() as $orderItem) {
            /** @var Mage_Sales_Model_Order_Item $orderItem */
            $additionalOptions = $orderItem->getProductOptionByCode('additional_options');

            if (! is_array($additionalOptions)) continue;

            $subscriptionOptions = false;
            foreach ($additionalOptions as $additionalOption) {
                if ($additionalOption['code'] == 'adyen_subscription') {
                    $subscriptionOptions = $additionalOption;
                    break;
                }
            }

            if (! $subscriptionOptions || $orderItem->getParentItemId()) continue;

            $productSubscription = Mage::getModel('adyen_subscription/product_subscription')->load($subscriptionOptions['option_value']);

            $subscriptionQty = $productSubscription->getQty();
            if ($subscriptionQty > 1) {
                $qty = $orderItem->getQtyOrdered() * $subscriptionQty;

                $orderItem = $this->_correctPrices($orderItem, $orderItem->getQtyOrdered(), $qty);
                $orderItem->setQtyOrdered($qty);
                $orderItem->save();

                foreach ($orderItem->getChildrenItems() as $childItem) {
                    /** @var Mage_Sales_Model_Order_Item $childItem */
                    $childItemQty = $childItem->getQtyOrdered() * $subscriptionQty;

                    $childItem = $this->_correctPrices($childItem, $childItem->getQtyOrdered(), $childItemQty);
                    $childItem->setQtyOrdered($childItemQty);
                    $childItem->save();
                }
            }
        }
    }

    /**
     * Set correct item prices ((original price / new qty) * old qty)
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param int $oldQty
     * @param int $newQty
     * @return Mage_Sales_Model_Order_Item
     */
    protected function _correctPrices($orderItem, $oldQty, $newQty)
    {
        $orderItem->setPrice(($orderItem->getPrice() / $newQty) * $oldQty);
        $orderItem->setBasePrice(($orderItem->getBasePrice() / $newQty) * $oldQty);
        $orderItem->setOriginalPrice(($orderItem->getOriginalPrice() / $newQty) * $oldQty);
        $orderItem->setBaseOriginalPrice(($orderItem->getBaseOriginalPrice() / $newQty) * $oldQty);

        $orderItem->setPriceInclTax(($orderItem->getPriceInclTax() / $newQty) * $oldQty);
        $orderItem->setBasePriceInclTax(($orderItem->getBasePriceInclTax() / $newQty) * $oldQty);

        return $orderItem;
    }

    /**
     * Set the right amount of qty on the order items when reordering or editing order.
     * The qty of the ordered items is divided by the 'qty in subscription'
     * amount of the selected product subscription, when editing order or the config
     * option is set to keep the subscription at reorder, else qty remains the same
     * but the subscription is deleted from the quote item.
     *
     * @event create_order_session_quote_initialized
     * @param Varien_Event_Observer $observer
     */
    public function calculateItemQtyReorder(Varien_Event_Observer $observer)
    {
        $subscriptionQuote = false;

        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Mage_Core_Model_Session $session */
        $session = $observer->getSessionQuote();

        $editOrder = Mage::app()->getRequest()->getControllerName() == 'sales_order_edit';

        if ($session->getData('subscription_quote_initialized')
            || (! $session->getReordered() && ! $editOrder)) {
            return;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $session->getQuote();

        foreach ($quote->getItemsCollection() as $quoteItem) {
            /** @var Mage_Sales_Model_Quote_Item $quoteItem */
            $option = $quoteItem->getOptionByCode('additional_options');

            if (! $option) continue;

            $additionalOptions = unserialize($option->getValue());

            $subscriptionOptions = false;
            foreach ($additionalOptions as $additionalOption) {
                if ($additionalOption['code'] == 'adyen_subscription') {
                    $subscriptionOptions = $additionalOption;
                    break;
                }
            }

            if (! $subscriptionOptions) continue;

            $productSubscription = Mage::getModel('adyen_subscription/product_subscription')->load($subscriptionOptions['option_value']);

            if (Mage::helper('adyen_subscription/config')->getReorderSubscription() || $editOrder) {
                if ($quoteItem->getParentItemId()) continue;

                // Only divide qty if reorder keeps subscription(s)
                // or is edit order action
                $subscriptionQty = $productSubscription->getQty();
                if ($subscriptionQty > 1) {
                    $qty = $quoteItem->getQty() / $subscriptionQty;

                    $quoteItem->setQty($qty);
                    $quoteItem->save();
                }
            }
            else {
                // Delete product subscriptions from products in reorder
                $newAdditionalOptions = [];
                foreach ($additionalOptions as $additionalOption) {
                    if ($additionalOption['code'] == 'adyen_subscription') {
                        continue;
                    }
                    $newAdditionalOptions[] = $additionalOption;
                }

                $option->setValue(serialize($newAdditionalOptions));

                $newBuyRequest = $quoteItem->getBuyRequest()->unsetData('adyen_subscription')->getData();
                $buyRequest = Mage::getModel('sales/quote_item_option')->setData([
                    'code'       => 'info_buyRequest',
                    'product_id' => $quoteItem->getProductId(),
                    'value'      => serialize($newBuyRequest)
                ]);

                $quoteItem->addOption($buyRequest);
                $quoteItem->save();
            }

            $subscriptionQuote = true;
        }

        if ($subscriptionQuote) {
            $quote->collectTotals();
            $session->setData('subscription_quote_initialized', true);
        }
    }


    /**
     * The BillingAgreement of an subscription can change for IDEAL and Sofort
     * When you do a recurring transaction for Ideal it will transform the payment to a SEPA payment
     * This will resolve in a new recurring_detail_reference that you need to use for future payments
     * so update the subscription with this new reference number
     *
     * @param Varien_Event_Observer $observer
     * @return string
     */
    public function updateBillingAgreementInSubscription(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();

        /** @var Varien_OBject $response */
        $response = $observer->getAdyenResponse();

        $eventCode = trim($response->getData('eventCode'));
        if($eventCode == Adyen_Payment_Model_Event::ADYEN_EVENT_RECURRING_CONTRACT) {

            // get billingAgreement of the order, an order is always connected to one agreement
            $billingAgreementId = $this->_getBillingAgreementId($order);

            if ($billingAgreementId) {
                // check if order has subscription(s)

                // subscription_order
                $subscriptionOrders = Mage::getModel('adyen_subscription/subscription_order')
                    ->getCollection()
                    ->addFieldToFilter('order_id', $order->getId());

                if ($subscriptionOrders->count() <= 0) {
                    return '';
                }

                // If the billingagreementId of the subscription does not match the new billingagreementId change the billingAgreementId to this new value
                foreach($subscriptionOrders as $subscriptionOrder) {

                    $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionOrder->getSubscriptionId());
                    $billingAgreementIdOfSubs = $subscription->getBillingAgreementId();
                    if($billingAgreementIdOfSubs != $billingAgreementId) {
                        try {
                            $subscription->setBillingAgreementId($billingAgreementId);
                            $subscription->save();

                            /*
                             *  it could be that there is already a new quote scheduled
                             *  so update this quote as well with the new recurringDetailReference
                             */
                            $quote = $subscription->getActiveQuote();
                            if($quote) {
                                Mage::getModel('adyen_subscription/service_subscription')->updateQuotePayment($subscription, $quote);
                            }
                        } catch(Exception $e) {
                            new Adyen_Subscription_Exception('Could not update subscrription '.$e->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Billing_Agreement
     */
    protected function _getBillingAgreementId(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $select = $connection->select();

        $select->from($resource->getTableName('sales/billing_agreement_order'));
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('agreement_id');
        $select->where('order_id = ?', $order->getId());
        $select->order(array('agreement_id DESC')); // important to get last agreement_id


        $billingAgreementId = $connection->fetchOne($select);
        if (! $billingAgreementId) {
            Adyen_Subscription_Exception::logException(
                new Adyen_Subscription_Exception('Could not find billing agreement for order '.$order->getIncrementId())
            );
            return null;
        }

        return $billingAgreementId;
    }


    /**
     * Check if billing agreement is not linked to a subscription. If this is the case return an exception
     * @param Varien_Event_Observer $observer
     */
    public function updateBillingAgreementStatus(Varien_Event_Observer $observer)
    {
        $agreement = $observer->getAgreement();
        $agreementId = $agreement->getId();

        $subscriptionCollection = Mage::getModel('adyen_subscription/subscription')
            ->getCollection()
            ->addFieldToFilter('billing_agreement_id', $agreementId);

        if ($subscriptionCollection->count() > 0) {
            Mage::throwException(Mage::helper('adyen_subscription')->__(
                'You cannot cancel this billing agreement because it is used for a subscription.'
            ));
        }
    }

    /**
     * Check if billing agreement is not linked to a subscription
     * If this is the case return an exception when trying to delete
     *
     * @param Varien_Event_Observer $observer
     */
    public function deleteBillingAgreement(Varien_Event_Observer $observer)
    {
        $agreement = $observer->getObject();

        if (! $agreement instanceof Adyen_Payment_Model_Billing_Agreement) {
            return;
        }

        $agreementId = $agreement->getId();

        $subscriptionCollection = Mage::getModel('adyen_subscription/subscription')
            ->getCollection()
            ->addFieldToFilter('billing_agreement_id', $agreementId);

        if ($subscriptionCollection->count() > 0) {
            Mage::throwException(Mage::helper('adyen_subscription')->__(
                'You cannot delete this billing agreement because it is used for a subscription.'
            ));
        }

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $select = $connection->select();

        $select->from(['a' => $resource->getTableName('sales/billing_agreement_order')]);
        $select->joinLeft(['order' => $resource->getTableName('sales/order')], 'a.order_id = order.entity_id');
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns(['a.order_id', 'order.state']);
        $select->where('agreement_id = ?', $agreementId);
        $select->where('order.state NOT IN (?)', [
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_COMPLETE,
        ]);

        if ($connection->fetchOne($select)) {
            Mage::throwException(Mage::helper('adyen_subscription')->__(
                'You cannot delete this billing agreement because it is used in active orders.'
            ));
        }
    }

    /**
     * Do not delete products that are used for active subscriptions
     * @param Varien_Event_Observer $observer
     */
    public function preventProductDeleteForSubscription(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        $collection = Mage::getModel('adyen_subscription/subscription_item')->getCollection();
        $resource = $collection->getResource();

        $collection->getSelect()->joinLeft(
            array('subscription' => $resource->getTable('adyen_subscription/subscription')),
            'main_table.subscription_id = subscription.entity_id'
        );

        $collection->addFieldToFilter('product_id', $product->getId());
        $collection->addFieldToFilter('subscription.status', Adyen_Subscription_Model_Subscription::STATUS_ACTIVE);


        $count = $collection->count();
        if ($count > 0) {

            Mage::throwException(Mage::helper('adyen_subscription')->__(
                'You cannot delete product (#%s) because it is attached to %s active subscription(s)', $product->getId(), $count
            ));
        }
    }

    /**
     * Do not allow guest checkout when adyen subscription item is added
     * @param Varien_Event_Observer $observer
     */
    public function isAllowedGuestCheckout(Varien_Event_Observer $observer)
    {
        if (Mage::helper('core')->isModuleEnabled('Ho_Customer') &&
            Mage::helper('ho_customer')->autoCreateCustomers())
        {
            return;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getQuote();

        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Varien_Object $result */
        $result = $observer->getResult();

        Mage::getSingleton('adyen_subscription/product_observer')->isQuoteAdyenSubscription($quote);
        if($quote->getData('_is_adyen_subscription')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $result->setIsAllowed(false);
        }
    }

    /**
     * Save changed customer address at customer quotes that are linked
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Exception
     */
    public function updateCustomerAddressAtQuotes(Varien_Event_Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Mage_Customer_Model_Address $address */
        $address = $observer->getCustomerAddress();

        $subscriptions = Mage::getModel('adyen_subscription/subscription')
            ->getCollection()
            ->addFieldToFilter('customer_id', $address->getCustomerId());

        foreach ($subscriptions as $subscription) {
            /** @var Adyen_Subscription_Model_Subscription $subscription */
            foreach ($subscription->getQuoteAdditionalCollection() as $quoteAdditional) {
                /** @var Adyen_Subscription_Model_Subscription_Quote $quoteAdditional */
                $quote = $quoteAdditional->getQuote();

                $billingAddress = $quote->getBillingAddress();
                if ($billingAddress->getCustomerAddressId() == $address->getId()) {
                    $billingAddress->addData($address->getData());
                    $billingAddress->save();
                }

                $shippingAddress = $quote->getShippingAddress();
                if ($shippingAddress->getCustomerAddressId() == $address->getId()) {
                    $shippingAddress->addData($address->getData());
                    $shippingAddress->save();
                }
            }
        }

        return $this;
    }
}
