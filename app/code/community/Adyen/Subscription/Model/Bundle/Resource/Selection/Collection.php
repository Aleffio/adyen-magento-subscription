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
 * Author: Adyen <magento@adyen.com>, Sander Mangel <s.mangel@fitforme.nl>
 */

class Adyen_Subscription_Model_Bundle_Resource_Selection_Collection extends Mage_Bundle_Model_Resource_Selection_Collection
{
    protected $qtyMultiplier = 1;

    /**
     * Adyen_Subscription_Model_Bundle_Resource_Selection_Collection constructor.
     * @param Mage_Core_Model_Resource_Abstract|null $params
     */
    public function __construct($params)
    {
        $this->qtyMultiplier = floatval($params['qtymultiplier']);

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as $item) {
            /**
             * recalculate the item selection qty using the subscription item multiplier
             * if the qty of the subscription is less than one.
             * This allows for quantities smaller than the bundle item fixed quantity
             */
            if (!$item->hasData('selection_qty_isset') && $this->qtyMultiplier < 1) {
                $item->setData('selection_qty_isset', true);
                $item->setData('selection_qty', floatval($item->getData('selection_qty')) * $this->qtyMultiplier);
            }
        }

        return $items;
    }
}