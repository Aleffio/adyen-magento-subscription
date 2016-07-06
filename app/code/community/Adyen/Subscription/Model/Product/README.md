# Model/Product #

### Adyen_Subscription_Model_Product_Observer

##### saveSubscriptionProductData
Save (or delete) product subscription data when saving a product.

##### addProductTypeSubscriptionHandle
Adds a product type XML handle on the frontend when viewing a product
which has product subscriptions (handle `PRODUCT_TYPE_adyen_subscription`).

##### isPaymentMethodActive
Checks if payment method can be used when a quote contains one or more
items with a product subscription.  
When editing a subscription in the backend, this method makes sure only
billing agreements are shown as payment methods.

##### isQuoteAdyenSubscription
Checks if given quote contains one or more items with a product subscription.
