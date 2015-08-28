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

/**
 * Collections limiter model
 *
 */
class Adyen_Subscription_Model_AdminGws_Collections extends Enterprise_AdminGws_Model_Collections
{
    /**
     * Add store_id attribute to filter of EAV-collection
     *
     * Extended to add 'main_table' prefix to store ID,
     * since we join the subscriptions at the order collection,
     * which also contains a store_id column.
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     */
    public function addStoreAttributeToFilter($collection)
    {
        $collection->addAttributeToFilter('main_table.store_id', array('in' => $this->_role->getStoreIds()));
    }
}
