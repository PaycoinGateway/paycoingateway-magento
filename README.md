paycoingateway-magento
================

Accept Paycoin on your Magento-powered website with PaycoinGateway. 


Installation
-------

Download the plugin and copy the 'app' folder to the root of your Magento installation.

If you don't have a PaycoinGateway account, sign up at https://www.paycoinggateway.com/admin/.

After installation, open Magento Admin and navigate to System > Configuration > Payment Methods:

Scroll down to 'PaycoinGateway' and follow the instructions. If you can't find 'PaycoinGateway', try clearing your Magento cache.


Custom events
-------

The plugin sends two events - 'paycoingateway_callback_received' when a callback is received, and 'paycoingateway_order_cancelled' when an order is cancelled. You can use these events to implement custom functionality on your Magento store.