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

// @todo Don't depend on Innoexts_Warehouse module
class Ho_Recurring_Model_Sales_Quote_Item extends Innoexts_Warehouse_Model_Sales_Quote_Item
{
    /**
     * Extended to return row total / qty instead of price incl. tax
     * when product is recurring
     *
     * @return float
     */
    public function getPriceInclTax()
    {
        if ($this->getBuyRequest()->getData('ho_recurring_profile')) {
            return $this->getRowTotalInclTax() / $this->getQty();
        }

        return $this->getData('price_incl_tax');
    }

    /**
     * Calculate item row total price
     *
     * Extended to remove rounding from total and baseTotal before multiplying by qty
     * when product is recurring.
     * This is done because we calculate the price by dividing the row total by the qty,
     * and if we round the price before multiplying, it won't be the exact row total we
     * started the calculation with (set in a recurring product profile).
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function calcRowTotal()
    {
        if ($this->getBuyRequest()->getData('ho_recurring_profile')) {
            $qty = $this->getTotalQty();

            $total = $this->getCalculationPriceOriginal() * $qty;
            $baseTotal = $this->getBaseCalculationPriceOriginal() * $qty;

            $this->setRowTotal($this->getStore()->roundPrice($total));
            $this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));

            return $this;
        }

        return parent::calcRowTotal();
    }
}
