[ ![Image](https://aplazame.com/static/img/banners/banner-728-white-woo.png "Aplazame") ](https://aplazame.com "Aplazame")

### Install

1. **Download** the latest plugin from [here](https://s3.eu-central-1.amazonaws.com/aplazame/modules/woocommerce/wild-style/aplazame.latest.zip) to local directory as `aplazame.latest.zip`.
2. Go to the Wordpress administration page, and then go to **Plugins** > **Add New**.
3. **Add new plugin** and select the `aplazame.latest.zip` file from your computer.

> Be sure that shipping info is enable, **Settings** > **Shipping** > **Shipping Options** and mark *enable shipping* by clicking on the checkbox.

### Usage

![config](docs/config.png)

* **Sandbox**: Determines if the module is on Sandbox mode.
* **Private API Key**: The Secret Key provided by Aplazame. You cannot share this key with anyone!!
* **Button**: The CSS Selector for Aplazame payment method. The default selector is `#payment ul li:has(input#payment_method_aplazame)`.
* **Product quantity CSS selector**: The CSS Selector for retrieve the product quantity in the product page. The default value is empty.
* **Product price CSS selector**: The CSS Selector for retrieve the product price in the product page. The default value is empty.
* **Variable product price CSS selector**: The CSS Selector for retrieve the variable product calculated price in the product page. The default selector is `#main [itemtype="http://schema.org/Product"] .single_variation_wrap .amount`.

> Be sure that on all fields you don't keep any whitespace. Otherwise the module can generate unexpected results.

### Release history

For new features check [this](HISTORY.md).


### Help

**Have a question about Aplazame?**

For any support request please drop us an email at [soporte@aplazame.com](mailto:soporte@aplazame.com?subject=Help me with the module).
