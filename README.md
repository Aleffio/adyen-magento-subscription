# Adyen Magento Subscription module

Adyen’s Subscription Plugin for Magento1 will help Adyen users such as gaming companies, digital content, and retailers, to give their customers the option to set up an automatic subscription, ensuring a significant positive effect on customer retention and recurring revenue. 

Payment methods available for recurring payment are credit cards, Paypal, SOFORT, and iDEAL, in combination with SEPA Direct Debit.

This extension will only work on Magento not on Magento2.



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

## Offical Releases
[Click here to see and download the official releases of the Adyen Payment subscription plug-in](https://github.com/Adyen/adyen-magento-subscription/releases)

## Getting started
[You need to install the Adyen Payment module 2.4.0 or higher as well](https://github.com/Adyen/magento/releases) <br />
<a href="https://docs.adyen.com/developers/plug-ins-and-partners/magento/magento-subscriptions" target="_blank">Click here to go into the manual</a>

## Support
For bugreports, please file a ticket, for store specific requests, please contact Adyen Magento support at magento@adyen.com

## Core Contributors
* H&O E-commerce specialists B.V.
* Adyen B.V.

## License
Open Software License ("OSL") v. 3.0

## Copyright ©
2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)