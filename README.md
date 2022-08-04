# Magento-Worldline-Sips2 payment module

Module de paiement atos worldline pour magento de MAD IT -- *** [PRO version here](https://marketplace.magento.com/madit-sips2.html).***


Ce module de payment magento supporte les intégrations bancaires suivantes:

- SHERLOCK de la LCL

- Sogénactif de la société générale

- Mercanet de BNP paribas

- Scellius de la banque postale

- Webaffaires du crédit du nord.


**This module is primarily intended for merchants using gateways abiding by worldline SIPS 2.0 API.**
Nonetheless, the migration mode will allow merchants coming from [SIPS v1](https://documentation.sips.worldline.com/en/announcements/end-sips-10.html) to switch effortlessly to the new WL SIPS v2.

<div align="center"><h6>Démo en vidéo</h6>
  <a href="https://youtu.be/kTahxkz1V10"><img src="https://user-images.githubusercontent.com/3765910/174391752-bad6a83e-e610-47b5-98bf-5bfe041c88b1.png" alt="démo module de paiment lcl sherlock"></a> 
</div>



Sips2 MADIT payment gateway for Magento 2


***Pro version of the module is available [on magento marketplace](https://marketplace.magento.com/madit-sips2.html).***


Tested on Magento 2.1.6 - 2.4.X (***Discounted version of the module is available [Here](https://www.madit.fr/shop/product/worldline-sips2-module-for-magento-2-6)***)



## Instructions for SIPS v2.0
As display on the screenshot above, merchant using the v 2.0 of the API can discard the instructions for v1.0 setup.
They will only require the `merchant_id` and the `secret_key` associated to have the module working.
Also, special attention should be paid to the SIPS version setting in the backoffice:
- **SIPS 2.0 (migration)** for merchants who have migrated from v 1.0 to v 2.0 interface keeping their merchant_id
- **SIPS 2.0 (transacationReference)** for merchants whose subscription begun under the v 2.0 premises and the shop is in transactionReference mode
- **SIPS 2.0 (auto)** for merchants whose subscription begun directly  the v 2.0 premises and whose TransactionReference should be generated by WL sips API.

[Here's the list of credit cards](https://documentation.sips.worldline.com/fr/cartes-de-test.html) that you can use on test environment.


Installation
============

```
php bin/magento module:enable Madit_sips2
php bin/magento setup:upgrade
php bin/magento cache:flush
```

The module can be configured under *Stores>Configuration>Sales>Payment methods>MADIT*


***La version complète du module est disponible sur [la marketplace magento adobe](https://marketplace.magento.com/madit-sips2.html).***

***Bénéficier d'un discount en achetant directement sur notre site [version discount](https://www.madit.fr/shop/product/worldline-sips2-module-for-magento-2-6)***

***Support disponible au travers de notre [**forum**](https://forum.madit.fr/) ou directement sur le [site de l'entreprise](https://www.madit.fr/en_US/contacteznous)***

[Need help? Don't hesitate fill this form](https://www.madit.fr/contacteznous)

