# Magento2-AtosSips-Sherlock-LCL

LCL Sherlock payment gateway for Magento 2

Tested on Magento 2.1.6 - 2.3

Copy ```Madit/Atos/view/res/atos_standard/logo``` into you media dir ```MAGENTO_DIR/pub/media```.
The merchant files are to be placed in ``` Madit/Atos/view/res/atos_standard/param```.
The  `pathfile` will be automatically generate in the param dir aswell.
The module can be configured through admin dashboard *store>configurations>sales>payment gateway*.

Installation
============

```php bin/magento2 module:enable Madit_Atos```
