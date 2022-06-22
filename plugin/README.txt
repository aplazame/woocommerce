=== Aplazame ===
Contributors: aplazame
Tags: aplazame,woocommerce,ecommerce,payment,checkout,credit,aplazar,financiar,financiera,financiación,pago aplazado,método de pago
Requires at least: 4.0.1
Tested up to: 6.0.0
Requires PHP: 5.3.0
Stable tag: 3.6.2
License: BSD-3-Clause
License URI: https://github.com/aplazame/woocommerce/blob/master/LICENSE

Aplazame is an instant credit payment method for online purchases that allows Magento stores to boost sales by 50% by using financing as a marketing lever.


== Description ==

= Introduction =

[Aplazame](https://aplazame.com) is an instant credit payment method for online purchases that allows WooCommerce stores to sell more and increase average ticket values with a risk-free solution. A simple, secure and flexible over time payment method integrated at the ecommerce checkout. Once you activate and successfully integrate Aplazame in your site, you will be able to offer financing as a payment method to your customers.

WooCommerce stores can highlight our instant credit solution in activating our widget. This widget is a minimal and fully customizable credit simulator that will be displayed on both your product description and shopping cart views. It will allow the ecommerce to easily communicate the customer the possibility of financing her/his purchases at every step of the customer journey.

Once Aplazame is integrated in WooCommerce stores, they can achieve:

* Boost sales by 50% and reach more customers
* Increase the average order value by 200%
* Improve conversion rate by 20%
* Aplazame guarantees all purchases and settles directly with merchants after order confirmation
* Reduce your cart abandonment rate by reducing price sensitivity and increasing affordability
* In order to start working with us in your store, you have to create a free merchant account with Aplazame. You have to do this before starting the configuration. You can create your own free account from this link: https://aplazame.com/#/account/signup

The plugin is free. Our business pricing is just a variable fee per transaction that ranges from 0,5 to 1,5% of the order amount depending on the volume (https://aplazame.com/prices/)

Aplazame operates in Spain. So if your store is located in Spain, you can integrate Aplazame as your payment method.

At this moment our service only use Euros.


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

#### [v3.6.1](https://github.com/aplazame/woocommerce/tree/v3.6.1) (2022-06-20)

* [ADD] Show/hide alternative widget option.

#### [v3.6.0](https://github.com/aplazame/woocommerce/tree/v3.6.0) (2021-12-22)

* [ADD] Show/hide alternative widget option.

#### [v3.5.0](https://github.com/aplazame/woocommerce/tree/v3.5.0) (2021-09-13)

* [ADD] Pay in 4 widget.

#### [v3.4.1](https://github.com/aplazame/woocommerce/tree/v3.4.1) (2021-06-08)

* [FIX] Retrocompatibility issue at article model.

#### [v3.4.0](https://github.com/aplazame/woocommerce/tree/v3.4.0) (2021-05-24)

* [ADD] Alignment options for widget v4.

#### [v3.3.0](https://github.com/aplazame/woocommerce/tree/v3.3.0) (2021-04-07)

* [ADD] Show/hide border option on product widget v4.

#### [v3.2.0](https://github.com/aplazame/woocommerce/tree/v3.2.0) (2021-02-24)

* [ADD] New widget (v4).

#### [v3.1.3](https://github.com/aplazame/woocommerce/tree/v3.1.3) (2021-01-28)

* CircleCI and SVN tweeks for WP plugin page.

#### [v3.1.2](https://github.com/aplazame/woocommerce/tree/v3.1.2) (2021-01-28)

* [FIX] WooCommerce requests timeout forced at 30 seconds (instead of 5 seconds, by default).

#### [v3.1.1](https://github.com/aplazame/woocommerce/tree/v3.1.1) (2021-01-20)

* Release for update assets on WP plugin page.

#### [v3.1.0](https://github.com/aplazame/woocommerce/tree/v3.1.0) (2021-01-14)

* [ADD] Instalments selector for widgets.
* [CHANGE] Marketplace assets.

#### [v3.0.0](https://github.com/aplazame/woocommerce/tree/v3.0.0) (2021-01-12)

* [ADD] Blended checkout.
* [FIX] Minor improvements.

#### [v2.2.4](https://github.com/aplazame/woocommerce/tree/v2.2.4) (2020-09-17)

* [FIX] Campaigns pagination.

#### [v2.2.3](https://github.com/aplazame/woocommerce/tree/v2.2.3) (2020-07-20)

* [FIX] Order confirmation at WC < 3.0.

#### [v2.2.2](https://github.com/aplazame/woocommerce/tree/v2.2.2) (2020-07-10)

* [FIX] Retro-compatibility issue at confirmation and other minor fixes.

#### [v2.2.1](https://github.com/aplazame/woocommerce/tree/v2.2.1) (2020-06-08)

* [FIX] Retro-compatibility issue.

#### [v2.2.0](https://github.com/aplazame/woocommerce/tree/v2.2.0) (2020-06-04)

* [ADD] Option to change title and description.

#### [v2.1.1](https://github.com/aplazame/woocommerce/tree/v2.1.1) (2020-03-16)

* [ADD] Hide params if product not available.
* [FIX] Code improvements.

#### [v2.1.0](https://github.com/aplazame/woocommerce/tree/v2.1.0) (2020-02-18)

* [ADD] Legal notice option to widget setup.

#### [v2.0.4](https://github.com/aplazame/woocommerce/tree/v2.0.4) (2020-02-03)

* [FIX] Minor fixes.

#### [v2.0.3](https://github.com/aplazame/woocommerce/tree/v2.0.3) (2020-01-14)

* [FIX] Deprecated checks.

#### [v2.0.2](https://github.com/aplazame/woocommerce/tree/v2.0.2) (2019-12-23)

* [ADD] Update history endpoint.
* [FIX] Various fixes and improvements.

#### [v2.0.1](https://github.com/aplazame/woocommerce/tree/v2.0.1) (2019-10-24)

* [FIX] Repository deploy.

#### [v2.0.0](https://github.com/aplazame/woocommerce/tree/v2.0.0) (2019-10-22)

* [ADD] 'Pay in 15 days' as payment method.

#### [v1.2.4](https://github.com/aplazame/woocommerce/tree/v1.2.4) (2019-08-22)

* [FIX] Payment method description for countries different than Spain.

#### [v1.2.3](https://github.com/aplazame/woocommerce/tree/v1.2.3) (2019-07-11)

* [FIX] Check if Aplazame is the current payment method.
* [ADD] JS async load.

#### [v1.2.2](https://github.com/aplazame/woocommerce/tree/v1.2.2) (2019-03-26)

* [FIX] Changed total to subtotal in article prices to maintain consistency with other attributes.

#### [v1.2.1](https://github.com/aplazame/woocommerce/tree/v1.2.1) (2018-12-12)

* [FIX] Variable products to campaigns.

#### [v1.2.0](https://github.com/aplazame/woocommerce/tree/v1.2.0) (2018-12-10)

* [ADD] Improve customization of widget place in both product and cart pages.
* [DEL] Hide finished campaigns.

#### [v1.1.1](https://github.com/aplazame/woocommerce/tree/v1.1.1) (2018-09-07)

* Fix compatibility with WooCommerce v2.

#### [v1.1.0](https://github.com/aplazame/woocommerce/tree/v1.1.0) (2018-08-20)

* Prevent conflicts with 3rd party systems who perform unsafe filtering

#### [v1.0.1](https://github.com/aplazame/woocommerce/tree/v1.0.1) (2018-07-31)

* Minor changes

#### [v1.0.0](https://github.com/aplazame/woocommerce/tree/v1.0.0) (2018-07-26)

* Redirect page has been removed.
* Many other improvements.

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


== Upgrade Notice ==

= 0.9.0 =
**The option to cancel/refund orders using the status select has been removed.**
Order refund can be performed using the “Refund” option under the order’s item list as usual.
