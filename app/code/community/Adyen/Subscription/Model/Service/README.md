# Model/Service #

### Adyen_Subscription_Model_Service_Order
_Methods for handling a Magento order_

##### createSubscription
Create one or more subscriptions for given order.  
The order is checked for items which contain a product subscription,
and these items are grouped by term. A different subscription will be
created for each term.

### Adyen_Subscription_Model_Service_Quote
_Methods for handling a Magento quote_

##### createOrder
Create an order based on a quote (the subscriptions' active quote) and
the linked subscription.  
This method is used by the cron for creating orders of subscriptions
which have reached the scheduled date. 

##### updateSubscription
Update the given subscription based on a quote.  
This method is used when editing a subscription. When editing a subscription,
the admin interface used is actually editing the active quote of the
subscription, this method applies the quote changes to the subscription.

##### updateQuotePayment
The additional info and cc type of a quote payment are not updated when
selecting another payment method while editing a subscription or subscription quote,
but they have to be updated for the payment method to be valid.  
This is used when updating a subscription.

### Adyen_Subscription_Model_Service_Subscription
_Methods for handling an Adyen Subscription_

##### createQuote
Create a quote based on the given subscription and saves it as the
active quote ('scheduled order') for the subscription.

##### updateQuotePayment
This is used for updating the payment method of an active quote of a
subscription when the billing agreement is changed.
