# Adyen_Subscription
Adyen Subscription plugin for Magento


## Dispatched events

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
        @param $quoteItem Mage_Sales_Model_Quote_Item