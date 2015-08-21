# Adyen_Subscription
Adyen Subscription plugin for Magento


## Dispatched events

**Adyen_Subscription_Model_Service_Subscription**

    30 adyen_subscription_service_createquote_before
        @param $subscription Adyen_Subscription_Model_Subscription

    94 adyen_subscription_service_createquote_add_item
        @param $item Mage_Sales_Model_Quote_Item
        @param $quote Mage_Sales_Model_Quote

    169 adyen_subscription_service_createquote_after
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $quote Mage_Sales_Model_Quote

    178 adyen_subscription_service_createquote_fail
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $status Adyen_Subscription_Model_Subscription::STATUS_QUOTE_ERROR
        @param $error string

    188 adyen_subscription_service_updatequotepayment_before
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $quote Mage_Sales_Model_Quote

    207 adyen_subscription_service_updatequotepayment_after
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $quote Mage_Sales_Model_Quote

    219 adyen_subscription_service_updatequotepayment_fail
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $status Adyen_Subscription_Model_Subscription::STATUS_QUOTE_ERROR
        @param $error string

**Adyen_Subscription_Model_Service_Quote**

    32 adyen_subscription_quote_createorder_before
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $quote Mage_Sales_Model_Quote
        
    32 adyen_subscription_quote_createorder_after
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $quote Mage_Sales_Model_Quote
        @param $order Mage_Sales_Model_Order
        
    99 adyen_subscription_quote_createorder_fail
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $status Adyen_Subscription_Model_Subscription::STATUS_PAYMENT_ERROR
        @param $error string
        
    107 adyen_subscription_quote_createorder_fail
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $status Adyen_Subscription_Model_Subscription::STATUS_ORDER_ERROR
        @param $error string
    
    114 adyen_subscription_quote_updatesubscription_before
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $quote Mage_Sales_Model_Quote
        
    212 adyen_subscription_quote_updatesubscription_add_item
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $item Adyen_Subscription_Model_Subscription_Item
        
    221 adyen_subscription_quote_updatesubscription_after
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $quote Mage_Sales_Model_Quote
        
    237 adyen_subscription_quote_updatequotepayment_before
        @param $billingAgreement Adyen_Payment_Model_Billing_Agreement
        @param $quote Mage_Sales_Model_Quote
     
    252 adyen_subscription_quote_updatequotepayment_after
        @param $billingAgreement Adyen_Payment_Model_Billing_Agreement
        @param $quote Mage_Sales_Model_Quote
    
    280 adyen_subscription_quote_getbillingagreement
        @param $billingAgreement Adyen_Payment_Model_Billing_Agreement
        @param $quote Mage_Sales_Model_Quote
    
    303 adyen_subscription_quote_getproductsubscription
        @param $productSubscription Adyen_Subscription_Model_Product_Subscription
        @param $item Mage_Sales_Model_Quote_Item
        
**Adyen_Subscription_Model_Service_Order**

    32 adyen_subscription_order_createsubscription_before
        @param $order Mage_Sales_Model_Order
        
    122 adyen_subscription_order_createsubscription_add_item
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $item Adyen_Subscription_Model_Subscription_Item	
        
    162 adyen_subscription_order_createsubscription_after
        @param $subscription Adyen_Subscription_Model_Subscription
        @param $order Mage_Sales_Model_Order
        
    198 adyen_subscription_quote_getbillingagreement
        @param $billingAgreement Mage_Sales_Billing_Agreement
        @param $order Mage_Sales_Model_Order
        
    221 adyen_subscription_order_getproductsubscription
        @param $productSubscription Adyen_Subscription_Model_Product_Subscription
        @param $item Mage_Sales_Model_Order_Item
	