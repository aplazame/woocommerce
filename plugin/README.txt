=== Aplazame ===
Contributors: aplazame
Tags: aplazame,woocommerce,ecommerce,payment,checkout,credit,aplazar,financiar,financiera,financiación,pago aplazado,método de pago
Requires at least: 4.0.1
Tested up to: 4.9
Requires PHP: 5.3.0
Stable tag: 0.9.0
License: BSD-3-Clause
License URI: https://github.com/aplazame/woocommerce/blob/master/LICENSE

Aplazame para WooCommerce, compra ahora y paga después


== Description ==

= Introduction =

[Aplazame](https://aplazame.com) is a consumer credit company, offers a payment system that can be used by online buyers to receive funding for their purchases.

The module allows the customer to defer the payment of his online purchases in ecommerces offering Aplazame as a payment method. Aplazame's mission is to increase sales of any online store eliminating any friction in the process.

Ease of implementation is the main objective we had in mind when developing Aplazame.

= Support =

For any support request please drop us an email at [soporte@aplazame.com](mailto:soporte@aplazame.com?subject=Help-me). Our support team is available 24/7.

*We hope you'll enjoy using Aplazame's WooCommerce plugin!*


== Installation ==

= Requirements =

*WooCommerce 2.3 or higher*

= Quick install =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Aplazame for WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type Aplazame for WooCommerce and click Search Plugins. Once you've found our plugin you can install it by simply clicking Install Now.

= Updating =

Automatic updates should work great for you.

= Usage =

* **Sandbox**: Determines if the module is on Sandbox mode.
* **Private API Key**: The Secret Key provided by Aplazame. You cannot share this key with anyone!!
* **Button**: The CSS Selector for Aplazame payment method. The default selector is `#payment ul li:has(input#payment_method_aplazame)`.
* **Product quantity CSS selector**: The CSS Selector for retrieve the product quantity in the product page. The default value is empty.
* **Product price CSS selector**: The CSS Selector for retrieve the product price in the product page. The default value is empty.
* **Variable product price CSS selector**: The CSS Selector for retrieve the variable product calculated price in the product page. The default selector is `#main [itemtype="http://schema.org/Product"] .single_variation_wrap .amount`.

> Be sure that on all fields you don't keep any whitespace. Otherwise the module can generate unexpected results.


== Frequently Asked Questions ==

= How Can I Get an API Keys? =
Just signup for a vendor account at [https://vendors.aplazame.com/u/signup](https://vendors.aplazame.com/u/signup)

= Can I cancel an order with Aplazame? =
No, you can refund the order for the total amount

= Can I refund an order with Aplazame? =
Of course, simply select an *"Aplazame refund"* in the product detail.


== Changelog ==

#### [v0.9.0](https://github.com/aplazame/woocommerce/tree/v0.9.0) (2018-02-08)

**The option to cancel/refund orders using the status select has been removed.**
You can still doing normal refunds using the "Refund" option below the order's item list.

* [ADD] Added a new settings for toggle the rendering of Aplazame's widget on product page.
* [ADD] Added a new settings for toggle the rendering of Aplazame's widget on cart page.
* [DEL] Removed the option of cancel the credit when order is manually set to *Cancelled*.
* [DEL] Removed the option of refund the credit when order is manually set to *Refunded*.

#### [v0.8.0](https://github.com/aplazame/woocommerce/tree/v0.8.0) (2018-01-11)

* Support for orders with pending status

#### [v0.7.1](https://github.com/aplazame/woocommerce/tree/v0.7.1) (2017-12-21)

* Minor changes

#### [v0.7.0](https://github.com/aplazame/woocommerce/tree/v0.7.0) (2017-12-21)

* [ADD] Private Key is automatically validated when set
* [ADD] Public Key is not longer needed to manual set
* [ADD] Many other compatibility improvements
* [FIX] Default customer date of birth

#### [v0.6.2](https://github.com/aplazame/woocommerce/tree/v0.6.2) (2017-11-29)

* Fix simulator does not calculate the price with taxes included

#### [v0.6.1](https://github.com/aplazame/woocommerce/tree/v0.6.1) (2017-07-06)

* Fix error on checkout payload

#### [v0.6.0](https://github.com/aplazame/woocommerce/tree/v0.6.0) (2017-04-10)

* Fix refunds.
* Add compatibility with WooCommerce v3

#### [v0.5.2](https://github.com/aplazame/woocommerce/tree/v0.5.2) (2017-02-23)

* Many fixes and improvements.

#### [v0.5.1](https://github.com/aplazame/woocommerce/tree/v0.5.1) (2017-02-22)

* Many fixes and improvements.

#### [v0.5.0](https://github.com/aplazame/woocommerce/tree/v0.5.0) (2017-02-22)

* Many fixes and improvements.

#### [v0.4.5](https://github.com/aplazame/woocommerce/tree/v0.4.5) (2016-11-24)

* [fixed] When return to shop current order is cancelled.

#### [v0.4.4](https://github.com/aplazame/woocommerce/tree/v0.4.4) (2016-10-31)

* Fix confirmation_url generation

#### [v0.4.3](https://github.com/aplazame/woocommerce/tree/v0.4.3) (2016-09-16)

* Fix aplazame-redirect for WP < 4.4

#### [v0.4.2](https://github.com/aplazame/woocommerce/tree/v0.4.2) (2016-09-14)

* Fix compatibility with WooCommerce < 2.4
* Tested up to WordPress 4.6

#### [v0.4.1](https://github.com/aplazame/woocommerce/tree/v0.4.1) (2016-08-18)

* Fix third party plugins conflict with aplazame redirect.

#### [v0.4.0](https://github.com/aplazame/woocommerce/tree/v0.4.0) (2016-08-16)

* Aplazame Campaigns.

#### [v0.3.0](https://github.com/aplazame/woocommerce/tree/v0.3.0) (2016-07-27)

* Many performance improvements.

#### [v0.2.0](https://github.com/aplazame/woocommerce/tree/v0.2.0) (2016-06-20)

* Add new settings for customize product price and product quantity CSS selectors.
* Remove `host` and `version` settings.
* Fix API error when refund.
* Fix product amount for products with fixed prices.

#### [v0.1.0](https://github.com/aplazame/woocommerce/tree/v0.1.0) (2016-06-13)

* Fix many warnings and other kind of improvements.
* Improvements for WooCommerce "Variable product"

#### [v0.0.8](https://github.com/aplazame/woocommerce/tree/v0.0.8) (2016-03-16)

* Widget data view

#### [v0.0.7](https://github.com/aplazame/woocommerce/tree/v0.0.7) (2016-02-17)

* Cart widget

#### [v0.0.6](https://github.com/aplazame/woocommerce/tree/v0.0.6) (2015-12-15)

* Fix order get_total_shipping method

#### [v0.0.5](https://github.com/aplazame/woocommerce/tree/v0.0.5) (2015-12-15)

* Fix PHP 5.3 compatibility
* Fix shipping division by zero
* Minor improvements

#### [v0.0.4](https://github.com/aplazame/woocommerce/tree/v0.0.4) (2015-09-25)

* Allow empty shipping
* Fix security, order session key
* Fix customer serializer

#### [v0.0.3](https://github.com/aplazame/woocommerce/tree/v0.0.3) (2015-09-15)

* Admin notices checks
* l10n review
* update composer.json
* make options

#### [v0.0.2](https://github.com/aplazame/woocommerce/tree/v0.0.2) (2015-09-14)

* l10n fix and review
* API exception handler
* process_refund on gateway class
* Full Readme.txt

#### [v0.0.1](https://github.com/aplazame/woocommerce/tree/v0.0.1) (2015-09-12)

* birth


== Upgrade Notice ==

= 0.9.0 =
**The option to cancel/refund orders using the status select has been removed.**
Order refund can be performed using the “Refund” option under the order’s item list as usual.

= 0.8.0 =
Now the orders status will keep in "Pending" while Aplazame finish to check all validations. No more manual actions are required!
