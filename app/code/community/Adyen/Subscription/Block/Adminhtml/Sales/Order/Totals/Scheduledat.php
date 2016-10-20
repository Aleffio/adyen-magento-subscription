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

class Adyen_Subscription_Block_Adminhtml_Sales_Order_Totals_Scheduledat extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{

    /**
     * Get short date syntax in local format
     * @return string
     */
    public function getDateTimeFormat()
    {
        return Varien_Date::convertZendToStrftime(
            Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), true, true
        );
    }

    /**
     * get the scheduled at date if available in quote
     * @return string
     */
    public function getScheduledAt()
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $this->getQuote();

        $deliveryDate = $quote->getScheduledAt();
        if (! $deliveryDate) {
            return '';
        }

        return Mage::helper('core')->formatDate($deliveryDate, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, true);
    }
}
