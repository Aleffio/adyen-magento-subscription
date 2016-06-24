# Model #

### Adyen_Subscription_Model_Cron

##### createSubscriptions
Create subscriptions of new orders which contain subscription products.

##### createQuotes
Create active quotes (scheduled orders) of subscriptions which have a
scheduled-at date which is within now and 2 weeks (by default, this term
can be changed in config under _Adyen Subscriptions > Advanced > Schedule Quotes Term_.

##### createOrders
Create orders of active quotes (scheduled orders) of subscriptions which
have a scheduled-at date which is in the past.

##### updatePrices
Updates prices of subscription items, when a price of a product subscription
is changed and the 'Update prices' checkbox was checked.

# Adyen_Subscription_Model_Observer #

##### calculateItemQty
Set the right amount of qty on the order items when placing an order.  
The ordered qty is multiplied by the 'qty in subscription' amount of the
selected subscription.

##### calculateItemQtyReorder
Set the right amount of qty on the order items when reordering or editing order.  
The qty of the ordered items is divided by the 'qty in subscription'
amount of the selected product subscription, when editing order or the config
option is set to keep the subscription at reorder, else qty remains the same
but the subscription is deleted from the quote item.

##### updateBillingAgreementInSubscription
The billing agreement of a subscription can change for iDEAL and Sofort.  
When you do a recurring transaction for iDeal it will transform the payment
to a SEPA payment.  
This will resolve in a new `recurring_detail_reference` that you need to
use for future payments, so the subscription needs to be updated with
this new reference number.

##### updateBillingAgreementStatus
Checks if the billing agreement that is trying to be canceled is linked
to a subscription. If this is the case, the agreement can't be canceled.

##### deleteBillingAgreement
Checks if the billing agreement that is trying to be deleted is linked
to a subscription. If this is the case, the agreement can't be deleted.

##### preventProductDeleteForSubscription
Checks if the product hat is trying to be deleted is linked to one or
more subscriptions. If this is the case, the product can't be deleted.

##### isAllowedGuestCheckout
Prevent guest checkout when customer has one or more subscription products
in their cart.

##### updateCustomerAddressAtQuotes
Save changed customer address at customer quotes that are linked.
