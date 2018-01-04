## Guest to Customer for Magento2

Quickly and easily convert existing guest checkout customers to registered customers. 
Perfect for reordering or modifying existing order without having to manually copying all of customer existing billing and shipping information.

## Customer Dashboard
![guest to customer - frontend](https://user-images.githubusercontent.com/1415141/34582810-438954f0-f163-11e7-83ad-c74844a1fbd4.gif)

## Sales Order Admin
![guest to customer - admin](https://user-images.githubusercontent.com/1415141/34582821-4e9f8882-f163-11e7-95fa-3022f240c276.gif)

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

© MagePal LLC.
