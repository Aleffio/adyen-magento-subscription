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
