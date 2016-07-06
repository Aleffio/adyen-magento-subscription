# Model/Catalog/Product/Price #

### Adyen_Subscription_Model_Catalog_Product_Price_Simple

##### getFinalPrice
Retrieves subscription price when product is a subscription product.  
First it checks if the given product is a subscription item, if no item
is found, it checks if the given product is a product subscription.  
Falls back to default Magento functionality otherwise.  
Note: When configured that catalog prices are including tax and subscription prices excluding tax,
the subscription item prices of new orders change when tax percentage is changed.

##### getTierPrice
Hides tier pricing when product is a subscription product.

### Adyen_Subscription_Model_Catalog_Product_Price_Configurable

##### getFinalPrice
See Adyen_Subscription_Model_Catalog_Product_Price_Simple::getFinalPrice.

### Adyen_Subscription_Model_Catalog_Product_Price_Bundle

##### getFinalPrice
See Adyen_Subscription_Model_Catalog_Product_Price_Simple::getFinalPrice.
