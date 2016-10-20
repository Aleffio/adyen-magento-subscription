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
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>
 */

class Adyen_Subscription_Model_Sales_Observers_Order
{

    /**
     * @param Varien_Event_Observer $o
     * @return $this
     */
    public function salesOrderLoadAfter(Varien_Event_Observer $o)
    {
        $this->_getScheduledDate($o);
        return $this;
    }

    /**
     * @param Varien_Event_Observer $o
     *
     * @return $this
     */
    public function adminhtmlSalesOrderCreateProcessDataBefore(Varien_Event_Observer $o)
    {
        $this->_addScheduledAtDate($o);

        return $this;
    }

    /**
     * Set default value for scheduled_at if not available
     * @param Varien_Event_Observer $o
     */
    protected function _getScheduledDate(Varien_Event_Observer $o)
    {
        $order = $o->getEvent()->getOrder();
        if ($order->getData('scheduled_at') == null){
            $order->setData('scheduled_at', $order->getData('created_at'));
        }
    }

    /**
     * Set scheduled_at date of order in postdata object
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    protected function _addScheduledAtDate(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Model_Sales_Order_Create $createModel */
        $createModel = $observer->getData('order_create_model');

        $quote = $createModel->getQuote();

        $postData = Mage::app()->getRequest()->getPost('order');
        if (!is_array($postData)) {
            return $this;
        }
        if (!array_key_exists('scheduled_at', $postData) && empty($postData['scheduled_at'])) {
            $postData['scheduled_at'] = time();
        }

        if (Mage::app()->getLocale()->getLocaleCode() != 'en_US') {
            /*
             * @todo make the date coversion more universal
             * Bugfix: Replace scheduled_at date slashes with dashes when locale is US
             * This is done because dates with slashes (US) are handled differently,
             * as noted in the documentation (see below), but UK dates also have slashes in their format (d/m/y).
             * @see http://php.net/manual/en/function.strtotime.php
             * Dates in the m/d/y or d-m-y formats are disambiguated by looking at the separator between the various components:
             * if the separator is a slash (/), then the American m/d/y is assumed; whereas if the separator is a dash (-) or a dot (.),
             * then the European d-m-y format is assumed.
             * If, however, the year is given in a two digit format and the separator is a dash (-), the date string is parsed as y-m-d.
             */
            $postData['scheduled_at'] = str_replace('/', '-', $postData['scheduled_at']);
        }

        $scheduledAt = Mage::getModel('core/date')->gmtDate(null, $postData['scheduled_at']);
        $quote->setScheduledAt($scheduledAt);

        return $this;
    }
}
