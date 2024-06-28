# AsamFastOrderSW6
This plugin adds a fast order form to your Shopware 6 store. The form allows customers to quickly order products by entering the product number and quantity.
The plugin is ideal for B2B customers who know exactly what they want to order and do not want to browse the entire store.

Table of contents
=================

  * [Features](#features)
  * [Installation](#installation)
  * [Configuration](#configuration)
  * [Usage](#usage)
  * [Support](#support)
  * [Testing](#testing)
  * [License](#license)


## Installation
1. Download the plugin from the Shopware store
2. Install the plugin in your Shopware backend under `Settings` > `System` > `Plugins`
3. Activate the plugin
4. Configure the plugin according to your needs
5. Done!

## Configuration
The plugin can be configured in the Shopware backend under `Settings` > `System` > `Plugins` > `AsamFastOrderSW6` > `Configuration`.

In the configuration, you can define the following settings:
- **Minimum input block**: The minimum number of input fields that must be displayed in the form.
- **Minimum characters before search**: The minimum number of characters that must be entered before the search for products is started via AJAX.
- **Search delay**: The delay in milliseconds to wait before starting the search for products via AJAX after the user has stopped typing.
- **Suggestions limit**: The maximum number of suggestions that are displayed in the search results.

## Usage
After the plugin has been installed and activated, a new menu item called `Fast order` will appear in the main navigation.
Here, customers or guests can enter the product number and quantity of the products they want to order. 
The products are then added to the shopping cart with a single click.

## Support

For questions, suggestions or feature requests, please contact us at ***
## Testing

You can test the plugin by installing it in your Shopware 6 store, configure the phpunit.xml.dist file and run the following command:

```bash
./vendor/bin/phpunit --configuration="custom/plugins/AsamFastOrderSW6"
```

## License
The plugin is released under the MIT license. The full license text can be found [here](LICENSE).
