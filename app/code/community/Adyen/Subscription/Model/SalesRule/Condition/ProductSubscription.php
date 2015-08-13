<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_CustomerSegment
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Segment condition for sales rules
 */
class Adyen_Subscription_Model_SalesRule_Condition_ProductSubscription extends Mage_Rule_Model_Condition_Abstract
{
    /**
     * @var string
     */
    protected $_inputType = 'string';

    /**
     * Value element type getter
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Render element HTML
     *
     * @return string
     */
    public function asHtml()
    {
        $this->_valueElement = $this->getValueElement();
        return $this->getTypeElementHtml()
            . Mage::helper('adyen_subscription')->__(
                'If Adyen Product Subscription ID %s %s',
                $this->getOperatorElementHtml(), $this->_valueElement->getHtml()
            )
            . $this->getRemoveLinkHtml()
            . '<div class="rule-chooser" url="' . $this->getValueElementChooserUrl() . '"></div>';
    }

    /**
     * Present selected values as array
     *
     * @return array
     */
    public function getValueParsed()
    {
        $value = $this->getData('value');
        $value = array_map('trim', explode(',',$value));
        return $value;
    }


    /**
     * Validate if qoute customer is assigned to role segments
     *
     * @param Mage_Sales_Model_Quote_Address|Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $isSubscription = Mage::getSingleton('adyen_subscription/product_observer')
            ->isQuoteAdyenSubscription($object->getQuote());

        if (! $isSubscription) {
            return false;
        }

        foreach ($object->getQuote()->getAllVisibleItems() as $quoteItem) {
            $subscriptionId = $quoteItem->getData('_adyen_subscription');
            if (! $subscriptionId) {
                continue;
            }
            $validated = $this->validateAttribute($subscriptionId);
            if ($validated) {
                return true;
            }
        }

        return false;
    }
}
