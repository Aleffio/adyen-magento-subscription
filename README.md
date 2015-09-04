# Adyen Magento Subscription module


## Magento CE installation

1. Copy files to your installation

  a. You can install the module via modman:
  ```bash
  modman clone git@github.com:Adyen/adyen-magento-subscription.git
  ```

  b. Or you can [download the latest release](https://github.com/Adyen/adyen-magento-subscription/releases) it and place it in you Magento root.
  
  c. Download from Magento Connect (not yet availble, waiting for approval Magento)

2. Flush your cache
3. Log in to your Magento Admin Panel

## Magento EE installation

1. Install the module like you would for an CE installation.
2. Copy the file `app/etc/modules/Adyen_Subscription_Enterprise.xml_disabled` to `app/etc/modules/Adyen_Subscription_Enterprise.xml`

## Getting started

[Click here to go into the manual](https://docs.adyen.com/display/TD/Adyen+Documentation)

## Support
For bugreports, please file a ticket, for store specific requests, please contact Adyen Magento support at magento@adyen.com

## Core Contributors
* H&O E-commerce specialists B.V.
* Adyen B.V.

## License
Open Software License ("OSL") v. 3.0

## Copyright ©
2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)