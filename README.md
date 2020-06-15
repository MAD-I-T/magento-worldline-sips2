# Magento2-AtosSips-Sherlock-LCL

LCL Sherlock payment gateway for Magento 2

Tested on Magento 2.1.6 - 2.3

Copy ```Madit/Atos/view/res/atos_standard/logo``` into you media dir ```MAGENTO_DIR/pub/media/atos_standard```.
```shell
 mkdir ${MAGENTO_DIR}/pub/media/atos_standard
 cp Madit/Atos/view/res/atos_standard/logo ${MAGENTO_DIR}/pub/media/atos_standard
```
The merchant files are to be placed in ``` Madit/Atos/view/res/atos_standard/param```.
The  `pathfile` will be automatically generate in the param dir during the first order processing (checkout the wiki for manual setting).
The module can be configured through admin dashboard *store>configurations>sales>payment gateway*.

Test accepted transaction infos:

```
CB Number : 4525485465452100
Expires : 01 2022
3 back digits : 100
```

Installation
============

```php bin/magento2 module:enable Madit_Atos```

 [Tutoriel d'installation et de configuration en français](https://www.madit.fr/r/Q1P)
