<a href="http://www.magepal.com" ><img src="https://image.ibb.co/dHBkYH/Magepal_logo.png" width="100" align="right" /></a>

## Guest to Customer for Magento2

In general E-commerce, shoppers do not like to create an account during checkout and often opt to checkout as a guest customer instead of a reguistered user. As a Magento store owner, if your online store sells products in various colors, sizes or other customizable items then you may be dealing with canceling and rewriting orders on a daily bases because of customers' change of heart after purchasing or they simply want to be able to get order status updates. Perfect for reordering or modifying existing order without having to manually copy all of your customer existing billing and shipping information.

Our free Guest to Customer extension for Magento2 quickly and seamlessly convert any guest order and transferred all order and customer information over as a registered customer and notifying the user on how to access their newly created customer account.

A customer can also log into their existing customer's dashboard and enter any existing order that they place previously as a guest and link that order to there current customer profile providing that they use the same email address and has the order number avaiable. 

#### Customer Dashboard
![guest to customer - frontend](https://image.ibb.co/d7qhfx/Customer_Dashboard_Guest_to_Customer_for_Magento2.gif)

#### Sales Order Admin
![guest to customer - admin](https://image.ibb.co/jeOPtH/Sales_Order_Admin_Guest_to_Customer_for_Magento2.gif)

## Installation

#### Step 1
##### Using Composer (recommended)

```
composer require magepal/magento2-guest-to-customer
```

##### Manual Installation
To install Guest to Customer for Magento2
 * Download the extension
 * Unzip the file
 * Create a folder {Magento root}/app/code/MagePal/GuestToCustomer
 * Copy the content from the unzip folder
 * Flush cache

#### Step 2 -  Enable
 * php -f bin/magento module:enable --clear-static-content MagePal_GuestToCustomer
 * php -f bin/magento setup:upgrade

#### Step 3 - Configuration
Log into your Magento Admin, then goto Stores -> Configuration -> MagePal -> Guest To Customer

Contribution
---
Want to contribute to this extension? The quickest way is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).


Support
---
If you encounter any problems or bugs, please open an issue on [GitHub](https://github.com/magepal/magento2-guest-to-customer/issues).

Need help setting up or want to customize this extension to meet your business needs? Please email support@magepal.com and if we like your idea we will add this feature for free or at a discounted rate.

© MagePal LLC. | [www.magepal.com](http:/www.magepal.com)
