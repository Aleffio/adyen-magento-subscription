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
            case Ho_Recurring_Model_System_Config_Source_Profile_Type::TYPE_ENABLED_ONLY_PROFILE:
            case Ho_Recurring_Model_System_Config_Source_Profile_Type::TYPE_ENABLED_ALLOW_STANDALONE:
                $this->_updateProductProfiles($product);
                break;
            default:
                $this->_deleteProductProfiles($product);
        }
    }


    protected function _updateProductProfiles(Mage_Catalog_Model_Product $product)
    {
        try {
            $productProfilesData = $this->_getRequest()->getPost('product_profile');

            /** @var array $productProfileIds */
            $productProfileIds = Mage::getModel('ho_recurring/product_profile')
                ->getCollection()
                ->addFieldToFilter('product_id', $product->getId())
                ->getAllIds();

            $i = 1;
            // Save profiles
            foreach ($productProfilesData as $id => $profileData) {
                $profile = Mage::getModel('ho_recurring/product_profile')->load($id);

                if (!$profile->getId()) {
                    $profile->setProductId($product->getId());
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
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ho_recurring')->__(
                    'Something went wrong when trying to save the recurring profile data: %s',
                    $e->getMessage()
                )
            );
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
       * @return Mage_Core_Controller_Request_Http
       */
      protected function _getRequest()
      {
          return Mage::app()->getRequest();
      }

    /**
     * Process `giftcard_amounts` attribute afterLoad logic on loading by collection
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftCard_Model_Observer
     */
    public function loadAttributesAfterCollectionLoad(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();

        foreach ($collection as $item) {
            if (Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD == $item->getTypeId()) {
                $attribute = $item->getResource()->getAttribute('giftcard_amounts');
                if ($attribute->getId()) {
                    $attribute->getBackend()->afterLoad($item);
                }
            }
        }
        return $this;
    }


//    /**
//    * Load recurring product profiles as custom option at product.
//    *
//    * @todo Loading this custom option causes the loading of this option in the Custom Options tab in the backend,
//    * @todo this must be avoided
//    *
//    * @event catalog_product_load_after
//    * @param Varien_Event_Observer $observer
//    */
//   public function addRecurringProductProfilesOption(Varien_Event_Observer $observer)
//   {
//       /** @var Mage_Catalog_Model_Product $product */
//       /** @noinspection PhpUndefinedMethodInspection */
//       $product = $observer->getProduct();
//
//       /** @var Mage_Catalog_Model_Product_Option $option */
//       $option = Mage::getModel('catalog/product_option')
//           ->setId(Ho_Recurring_Model_Product_Profile::CUSTOM_OPTION_ID)
//           ->setProductId($product->getId())
//           ->setType(Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO)
//           ->setTitle(Mage::helper('ho_recurring')->__('Recurring Profile'))
//           ->setProduct($product);
//
//       $recurringProductProfiles = Mage::getModel('ho_recurring/product_profile')
//           ->getCollection()
//           ->addFieldToFilter('product_id', $product->getId());
//
//       $i = 0;
//       foreach ($recurringProductProfiles as $productProfile) {
//           /** @var Ho_Recurring_Model_Product_Profile $productProfile */
//           $value = Mage::getModel('catalog/product_option_value')->setData(
//                   array(
//                       'option_type_id'        => $productProfile->getId(),
//                       'sort_order'            => $i,
//                       'default_title'         => $productProfile->getLabel(),
//                       'title'                 => $productProfile->getLabel(),
//                       'default_price'         => $productProfile->getPrice(),
//                       'default_price_type'    => 'fixed',
//                       'price'                 => $productProfile->getPrice(),
//                       'price_type'            => 'fixed',
//                   )
//               )->setOption($option);
//
//           $option->addValue($value);
//           $i++;
//       }
//
//       // Add the recurring profile option to the product
//       $product->addOption($option);
//
//       // Set the has_options attribute to true, or else the custom options won't be loaded on the frontend
//       /** @noinspection PhpUndefinedMethodInspection */
//       $product->setHasOptions(true);
//       /** @noinspection PhpUndefinedMethodInspection */
//       $product->setRequiredOptions(true);
//   }
//
//   /**
//    * Add the selected recurring product profile to the quote item, when one is selected
//    *
//    *
//    * @event catalog_product_load_after @todo use a better event, this gets called way to often.
//    * @param Varien_Event_Observer $observer
//    */
//   public function addRecurringProductProfileToQuote(Varien_Event_Observer $observer)
//   {
//       /** @var Mage_Checkout_CartController $action */
//       $action = Mage::app()->getFrontController()->getAction();
//
//       if (! $action || ! in_array($action->getFullActionName(), ['checkout_cart_updateItemOptions', 'checkout_cart_add'])) {
//           return;
//       }
//
//       $productId = $action->getRequest()->getParam('product');
//       $options = $action->getRequest()->getParam('options');
//       if (! $options) {
//           return;
//       }
//
//       if (! array_key_exists(Ho_Recurring_Model_Product_Profile::CUSTOM_OPTION_ID, $options)) {
//           return;
//       }
//
//       $recurringOption = $options[Ho_Recurring_Model_Product_Profile::CUSTOM_OPTION_ID];
//
//       /** @var Mage_Catalog_Model_Product $product */
//       /** @noinspection PhpUndefinedMethodInspection */
//       $product = $observer->getProduct();
//
//       if ($product->getId() != $productId) {
//           // Only add custom options if this is the product that is actually added to the cart
//           // This is done because there can be other products added to the cart automatically after
//           // a product is added, at which we don't want to save the additional recurring options
//           return;
//       }
//
//       $additionalOptions = array();
//       if ($additionalOption = $product->getCustomOption('additional_options')) {
//           $additionalOptions = (array) unserialize($additionalOption->getValue());
//       }
//
//       // Add the product profile ID to the additional options array
//       $additionalOptions[] = array(
//           'label' => Ho_Recurring_Model_Product_Profile::CUSTOM_OPTION_ID,
//           'value' => $recurringOption,
//       );
//
//       $product->addCustomOption('additional_options', serialize($additionalOptions));
//  }
//
//   /**
//    * Save additional (recurring) product options (added in addRecurringProductProfileToQuote)
//    * from quote items to order items
//    *
//    * @event sales_convert_quote_item_to_order_item
//    * @param Varien_Event_Observer $observer
//    */
//   public function addRecurringProductProfileToOrder(Varien_Event_Observer $observer)
//   {
//       /** @var Mage_Sales_Model_Quote_Item $quoteItem */
//       /** @noinspection PhpUndefinedMethodInspection */
//       $quoteItem = $observer->getItem();
//       /** @var Mage_Sales_Model_Order_Item $orderItem */
//       /** @noinspection PhpUndefinedMethodInspection */
//       $orderItem = $observer->getOrderItem();
//
//       if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
//           $options = $orderItem->getProductOptions();
//
//           $options['additional_options'] = unserialize($additionalOptions->getValue());
//
//           $orderItem->setProductOptions($options);
//       }
//   }
}