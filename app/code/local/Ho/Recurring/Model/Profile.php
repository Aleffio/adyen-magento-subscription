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

/**
 * Class Ho_Recurring_Model_Profile
 *
 * @method string getErrorMessage()
 * @method Ho_Recurring_Model_Profile setErrorMessage(string $value)
 * @method string getStoreId()
 * @method Ho_Recurring_Model_Profile setStoreId(string $value)
 * @method string getNextOrderAt()
 * @method Ho_Recurring_Model_Profile setNextOrderAt(string $value)
 * @method string getCreatedAt()
 * @method Ho_Recurring_Model_Profile setCreatedAt(string $value)
 * @method int getEntityId()
 * @method Ho_Recurring_Model_Profile setEntityId(int $value)
 * @method string getStockId()
 * @method Ho_Recurring_Model_Profile setStockId(string $value)
 * @method int getBillingAgreementId()
 * @method Ho_Recurring_Model_Profile setBillingAgreementId(int $value)
 * @method string getShippingMethod()
 * @method Ho_Recurring_Model_Profile setShippingMethod(string $value)
 * @method int getTerm()
 * @method Ho_Recurring_Model_Profile setTerm(int $value)
 * @method string getCustomerName()
 * @method Ho_Recurring_Model_Profile setCustomerName(string $value)
 * @method string getEndsAt()
 * @method Ho_Recurring_Model_Profile setEndsAt(string $value)
 * @method int getCustomerId()
 * @method Ho_Recurring_Model_Profile setCustomerId(int $value)
 * @method int getOrderId()
 * @method Ho_Recurring_Model_Profile setOrderId(int $value)
 * @method string getTermType()
 * @method Ho_Recurring_Model_Profile setTermType(string $value)
 * @method string getStatus()
 * @method Ho_Recurring_Model_Profile setStatus(string $value)
 */
class Ho_Recurring_Model_Profile extends Mage_Core_Model_Abstract
{
    const STATUS_ACTIVE             = 'active';
    const STATUS_QUOTE_ERROR        = 'quote_error';
    const STATUS_ORDER_ERROR        = 'order_error';
    const STATUS_CANCELED           = 'canceled';
    const STATUS_EXPIRED            = 'expired';
    const STATUS_AWAITING_PAYMENT   = 'awaiting_payment';
    const STATUS_PAYMENT_ERROR     = 'payment_error';

    protected function _construct ()
    {
        $this->_init('ho_recurring/profile');
    }

    public function getIncrementId()
    {
        return $this->getId();
    }

    /**
     * @return Ho_Recurring_Model_Profile_Quote
     */
    protected function _getActiveQuoteAdditional()
    {
        if (! $this->hasData('_active_quote_additional')) {
            $quoteAdd = Mage::getResourceModel('ho_recurring/profile_quote_collection')
                        ->addFieldToFilter('profile_id', $this->getId())
                        ->addFieldToFilter('order_id', ['null' => true])
                        ->getFirstItem();
            $this->setData('_active_quote_additional', $quoteAdd);
        }
        return $this->getData('_active_quote_additional');
    }


    /**
     * Only one quote of each profile can be saved
     * @param bool $instantiateNew
     * @return Ho_Recurring_Model_Profile_Quote
     */
    public function getActiveQuoteAdditional($instantiateNew = false)
    {
        $quoteAdditional = $this->_getActiveQuoteAdditional();

        if (! $quoteAdditional || ! $quoteAdditional->getId()) {
            if (! $instantiateNew) {
                return null;
            }
            $quoteAdditional = Mage::getModel('ho_recurring/profile_quote');
        }

        $quoteAdditional
            ->setProfile($this)
            ->setQuote($this->getActiveQuote());

        return $quoteAdditional;
    }

    /**
     * @return Ho_Recurring_Model_Resource_Profile_Quote_Collection
     */
    public function getQuoteAdditionalCollection()
    {
        return Mage::getResourceModel('ho_recurring/profile_quote_collection')
            ->addFieldToFilter('profile_id', $this->getId());
    }


    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Ho_Recurring_Model_Profile_Order|null|Varien_Object
     * @throws Exception
     */
    public function getOrderAdditional(Mage_Sales_Model_Order $order, $instantiateNew = false)
    {
        $orderAdditional = Mage::getModel('ho_recurring/profile_order')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId())
            ->addFieldToFilter('order_id', $order->getId())
            ->getFirstItem();

        if (!$orderAdditional->getId()) {
            if (! $instantiateNew) {
                return null;
            }
            $orderAdditional = Mage::getModel('ho_recurring/profile_order');
        }

        $orderAdditional->setOrder($order)->setProfile($this);
        return $orderAdditional;
    }


    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (! $this->hasData('_customer')) {
            $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
            $this->setData('_customer', $customer);
        }

        return $this->_getData('_customer');
    }

    /**
     * @param bool $active
     * @return Ho_Recurring_Model_Resource_Profile_Item_Collection
     */
    public function getItemCollection($active = true)
    {
        $items = Mage::getResourceModel('ho_recurring/profile_item_collection')
            ->addFieldToFilter('profile_id', $this->getId());

        if ($active) {
            $items->addFieldToFilter('status', Ho_Recurring_Model_Profile_Item::STATUS_ACTIVE);
        }

        return $items;
    }

    /**
     * @deprecated Please use getItemCollection
     * @param bool $active
     * @return Ho_Recurring_Model_Resource_Profile_Item_Collection
     */
    public function getItems($active = true)
    {
        return $this->getItemCollection($active);
    }

    /**
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getOrders()
    {
        return Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter('entity_id', array('in' => $this->getOrderIds()));
    }

    /**
     * @return array
     */
    public function getOrderIds()
    {
        $profileOrders = Mage::getModel('ho_recurring/profile_order')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId());

        $orderIds = array();
        foreach ($profileOrders as $profileOrder) {
            $orderIds[] = $profileOrder->getOrderId();
        }

        return $orderIds;
    }

    public function setActiveQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->setData('_active_quote', $quote);
        return $this;
    }
    /**
     * @return Mage_Sales_Model_Quote|null
     */
    public function getActiveQuote()
    {
        if (! $this->hasData('_active_quote')) {
            /** @var Ho_Recurring_Model_Profile_Quote $quoteAdditional */
            $quoteAdditional = $this->_getActiveQuoteAdditional();

            if (! $quoteAdditional || ! $quoteAdditional->getId()) {
                $this->setData('_active_quote', null);
                return null;
            }

            // Note: The quote won't load if we don't set the store ID
            $quote = Mage::getModel('sales/quote')
                ->setStoreId($this->getStoreId())
                ->load($quoteAdditional->getQuoteId());

            $this->setData('_active_quote', $quote);
        }

        return $this->getData('_active_quote');
    }

    /**
     * @deprecated All references to the original order must be removed.
     * @return Mage_Sales_Model_Order
     */
    public function getOriginalOrder()
    {
        return Mage::getModel('sales/order')->load($this->getOrderId());
    }

    public function calculateNextScheduleDate($asObject = false)
    {
        /** @var Ho_Recurring_Model_Profile_Quote $quoteAddCollection */
        $latestQuoteSchedule = $this->getQuoteAdditionalCollection()
            ->addFieldToFilter('order_id', ['notnull' => true])
            ->setOrder('scheduled_at', Varien_Data_Collection::SORT_ORDER_DESC)
            ->getFirstItem();

        $lastScheduleDate = $this->getCreatedAt();
        if ($latestQuoteSchedule->getId()) {
            $lastScheduleDate = $latestQuoteSchedule->getScheduledAt();
        }

        $timezone = new DateTimeZone(Mage::getStoreConfig(
            Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE
        ));
        $schedule = new DateTime($lastScheduleDate, $timezone);

        $dateInterval = null;
        switch ($this->getTermType()) {
            case Ho_Recurring_Model_Product_Profile::TERM_TYPE_DAY:
                $dateInterval = new DateInterval(sprintf('P%sD',$this->getTerm()));
                break;
            case Ho_Recurring_Model_Product_Profile::TERM_TYPE_WEEK:
                $dateInterval = new DateInterval(sprintf('P%sW',$this->getTerm()));
                break;
            case Ho_Recurring_Model_Product_Profile::TERM_TYPE_MONTH:
                $dateInterval = new DateInterval(sprintf('P%sM',$this->getTerm()));
                break;
            case Ho_Recurring_Model_Product_Profile::TERM_TYPE_QUARTER:
                $dateInterval = new DateInterval(sprintf('P%sM',$this->getTerm()*3));
                break;
            case Ho_Recurring_Model_Product_Profile::TERM_TYPE_YEAR:
                $dateInterval = new DateInterval(sprintf('P%sY',$this->getTerm()));
                break;
        }
        if (! isset($dateInterval)) {
            Ho_Recurring_Exception::throwException('Could not calculate a correct date interval');
        }

        $schedule->add($dateInterval);

        if ($asObject) {
            return $schedule;
        }

        return $schedule->format('Y-m-d H:i:s');
    }



    /**
     * @return Adyen_Payment_Model_Billing_Agreement
     */
    public function getBillingAgreement()
    {
        return Mage::getModel('adyen/billing_agreement')->load($this->getBillingAgreementId());
    }

    /**
     * @throws Exception
     */
    public function setActive()
    {
        $this->setStatus(self::STATUS_ACTIVE);
        $this->setErrorMessage(null);
        return $this;
    }

    /**
     * @return array
     */
    public static function getStatuses()
    {
        $helper = Mage::helper('ho_recurring');

        return [
            self::STATUS_ACTIVE             => $helper->__('Active'),
            self::STATUS_QUOTE_ERROR        => $helper->__('Quote Creation Error'),
            self::STATUS_ORDER_ERROR        => $helper->__('Order Creation Error'),
            self::STATUS_CANCELED           => $helper->__('Canceled'),
            self::STATUS_EXPIRED            => $helper->__('Expired'),
            self::STATUS_AWAITING_PAYMENT   => $helper->__('Awaiting Payment'),
            self::STATUS_PAYMENT_ERROR     => $helper->__('Payment Error'),
        ];
    }


    /**
     * @return array
     */
    public static function getScheduleQuoteStatuses()
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_QUOTE_ERROR
        ];
    }


    /**
     * @return array
     */
    public static function getPlaceOrderStatuses()
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_ORDER_ERROR,
            self::STATUS_PAYMENT_ERROR
        ];
    }


    /**
     * @return array
     */
    public static function getInactiveStatuses()
    {
        return [
            self::STATUS_CANCELED,
            self::STATUS_EXPIRED,
            self::STATUS_PAYMENT_ERROR
        ];
    }

    /**
     * @param string|null $status
     * @return string
     */
    public function getStatusLabel($status = null)
    {
        return self::getStatuses()[$status ? $status : $this->getStatus()];
    }

    /**
     * @return array
     */
    public static function getStatusColors()
    {
        return array(
            self::STATUS_ACTIVE             => 'green',
            self::STATUS_QUOTE_ERROR        => 'red',
            self::STATUS_ORDER_ERROR        => 'red',
            self::STATUS_CANCELED           => 'lightgray',
            self::STATUS_EXPIRED            => 'orange',
            self::STATUS_AWAITING_PAYMENT   => 'blue',
            self::STATUS_PAYMENT_ERROR     => 'orange',
        );
    }

    /**
     * @param string|null $status
     * @return string
     */
    public function getStatusColor($status = null)
    {
        return self::getStatusColors()[$status ? $status : $this->getStatus()];
    }

    /**
     * @param string|null $status
     * @return string
     */
    public function renderStatusBar($status = null)
    {
        if (is_null($status)) {
            $status = $this->getStatus();
        }

        $class = sprintf('status-bar status-bar-%s', $this->getStatusColor($status));

        return '<span class="' . $class . '"><span>' . $this->getStatusLabel($status) . '</span></span>';
    }

    /**
     * @return array
     */
    public function getTermTypes()
    {
        return Mage::getModel('ho_recurring/product_profile')->getTermTypes();
    }

    /**
     * @return string
     */
    public function getTermLabel()
    {
        return $this->getTerm() . ' ' . $this->getTermTypes()[$this->getTermType()];
    }


    /**
     * @todo remove hard dependency on original order
     * @return array
     */
    public function getBillingAddressData()
    {
        return $this->getOriginalOrder()->getBillingAddress()->getData();
    }

    /**
     * @todo remove hard dependency on original order
     * @return array
     */
    public function getShippingAddressData()
    {
        return $this->getOriginalOrder()->getShippingAddress()->getData();
    }

    public function canCancel()
    {
        return $this->getStatus() != self::STATUS_CANCELED;
    }


    /**
     * @return bool
     */
    public function canCreateQuote()
    {
        if ($this->getActiveQuote()) {
            return false;
        }

        if (! in_array($this->getStatus(), self::getScheduleQuoteStatuses())) {
            return false;
        }

        return true;
    }


    /**
     * @return bool
     */
    public function canCreateOrder()
    {
        if (! $this->getActiveQuote()) {
            return false;
        }

        if (! in_array($this->getStatus(), self::getPlaceOrderStatuses())) {
            return false;
        }

        return true;
    }

    public function getCreatedAtFormatted()
    {
        /** @noinspection PhpParamsInspection */
        return Mage::helper('core')->formatDate($this->getCreatedAt(), 'medium', true);
    }

}
