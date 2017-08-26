<?php

namespace Cymit\Atos\Model;
use  Magento\Payment\Model\Method\AbstractMethod;
use \Magento\Sales\Model\Order;
class Config extends \Magento\Framework\DataObject
{

    const PAYMENT_ACTION_CAPTURE = 'AUTHOR_CAPTURE';
    const PAYMENT_ACTION_AUTHORIZE = 'VALIDATION';

    protected $_method;
    protected $_merchantId;
    protected $moduleDirReader;
    protected $filesApi;
    protected $scopeConfig;
    protected $ccType;
    protected $storeManager;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Cymit\Atos\Model\Api\Files $filesApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Cymit\Atos\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->moduleDirReader = $moduleDirReader;
        $this->filesApi = $filesApi;
        $this->scopeConfig = $scopeConfig;
        $this->ccType = $ccType;
        $this->storeManager = $storeManager;
        parent::__construct($data);
    }

    /* @return \Cymit\Atos\Model\Config */
    public function initMethod($method)
    {
        if (empty($this->_method)) {
            $this->_method = $method;
        }
        return $this;
    }

    /**
     * Mapper from Atos/Sips Standard payment actions to Magento payment actions
     *
     * @return string|null
     */
    public function getPaymentAction($action)
    {
        switch ($action) {
            case AbstractMethod::ACTION_AUTHORIZE:
                return self::PAYMENT_ACTION_AUTHORIZE;
            case AbstractMethod::ACTION_AUTHORIZE_CAPTURE :
                return self::PAYMENT_ACTION_CAPTURE;
        }
    }

    /**
     * Payment actions source getter
     *
     * @return array
     */
    public function getPaymentActions()
    {
        $paymentActions = array(
            AbstractMethod::ACTION_AUTHORIZE_CAPTURE => __('Author Capture'),
            AbstractMethod::ACTION_AUTHORIZE => __('Validation')
        );
        return $paymentActions;
    }

    /**
     * Get certificate
     *
     * @return string
     */
    public function getCertificate() {
        return $this->moduleDirReader->getModuleDir('','Cymit_Atos').'view/res/atos'. $this->_method . '/param/certif.fr.014295303911111.php';
    }


    /**
     * Get pathfile path
     *
     * @return string
     */
    public function getPathfile()
    {
        $fileName = 'pathfile';// . $this->getMerchantId();
        //$directoryPath = Mage::getBaseDir('lib') . DS . 'atos' . DS . 'param' . DS;
        $directoryPath = $this->moduleDirReader->getModuleDir('','Cymit_Atos').'/view/res/'. $this->_method . '/param/';
        $path = $directoryPath . $fileName;

        if (!file_exists($path)) {
            $this->filesApi->generatePathfileFile(
                    $this->getMerchantId(), $fileName, $directoryPath, pathinfo($this->getCertificate(), PATHINFO_EXTENSION)
            );
        }

        if (!file_exists($directoryPath . 'parmcom.' . $this->getMerchantId())) {
            $data = array(
                'auto_response_url' => $this->getAutomaticResponseUrl(),
                'cancel_url' => $this->getCancelReturnUrl(),
                'return_url' => $this->getNormalReturnUrl(),
                'card_list' => implode(',', $this->ccType->getCardValues()),
                'currency' => $this->getCurrencyCode($this->storeManager->getStore()->getCurrentCurrency()->getCode()),
                'language' => $this->getLanguageCode(),
                'merchant_country' => $this->getMerchantCountry(),
                'merchant_language' => $this->getLanguageCode(),
                'payment_means' => implode(',2,', $this->ccType->getCardValues()) . ',2'
            );

            $this->filesApi->generateParmcomFile('parmcom.' . $this->getMerchantId(), $directoryPath, $data);
        }

        return $path;
    }

    /**
     * Get merchant ID
     *
     * @return string
     */
    public function getMerchantId()
    {
        if (empty($this->_merchantId)) {
            $matches = array();
            preg_match("/certif.[a-z]{2}.[0-9]+/", $this->getCertificate(), $matches);
            if (isset($matches[0])) {
                $merchantId = explode('.', $matches[0]);
                if (array_key_exists('2', $merchantId))
                    $this->_merchantId = $merchantId[2];
            }
        }
        return $this->_merchantId;
    }

    /**
     * Get merchant country code
     *
     * @return string
     */
    public function getMerchantCountry()
    {
        $countries = $this->scopeConfig->getValue('general/country');
        $currentCountryCode = strtolower($countries['default']);
        $atosConfigCountries = $this->getMerchantCountries();

        if (count($atosConfigCountries) === 1) {
            return strtolower($atosConfigCountries[0]);
        }

        if (array_key_exists($currentCountryCode, $atosConfigCountries)) {
            $code = array_keys($atosConfigCountries);
            $key = array_search($currentCountryCode, $code);

            return strtolower($code[$key]);
        }

        return 'fr';
    }

    /**
     * Get Atos/Sips authorized countries
     *
     * @return array
     */
    public function getMerchantCountries()
    {
        /*
        $countries = array();
        foreach (Mage::getConfig()->getNode('global/payment/atos/merchant_country')->asArray() as $data) {
            $countries[$data['code']] = $data['name'];
        }
        */

        return array("fr"=>"France", "es" =>"Espagne");
    }

    /**
     * Get currency code
     *
     * @return string|boolean
     */
    public function getCurrencyCode($currentCurrencyCode)
    {
        $atosConfigCurrencies = $this->getCurrencies();

        if (array_key_exists($currentCurrencyCode, $atosConfigCurrencies))
            return $atosConfigCurrencies[$currentCurrencyCode];
        else
            return false;
    }

    /**
     * Get Atos/Sips authorized currencies
     *
     * @return array
     */
    public function getCurrencies()
    {
        $currencies = array(
            "EUR" =>"978"
        );
        /*
        foreach (Mage::getConfig()->getNode('global/payment/atos/currencies')->asArray() as $data) {
            $currencies[$data['iso']] = $data['code'];
        }
        */

        return $currencies;
    }

    /**
     * Get language code
     *
     * @return string
     */
    public function getLanguageCode()
    {
        $language = substr($this->scopeConfig->getValue('general/locale/code'), 0, 2);
        $atosConfigLanguages = $this->getLanguages();

        if (count($atosConfigLanguages) === 1) {
            return strtolower($atosConfigLanguages[0]);
        }

        if (array_key_exists($language, $atosConfigLanguages)) {
            $code = array_keys($atosConfigLanguages);
            $key = array_search($language, $code);

            return strtolower($code[$key]);
        }


        return 'fr';
    }

    /**
     * Get Atos/Sips authorized languages
     *
     * @return array
     */
    public function getLanguages()
    {
        $languages = array(
            "fr" => "Français",
            "en" => "Anglais"
        );
        /*
        foreach (Mage::getConfig()->getNode('global/payment/atos/languages')->asArray() as $data) {
            $languages[$data['code']] = $data['name'];
        }
        */

        return $languages;
    }

    /**
     * Get selected data field
     *
     * @return string
     */
    public function getSelectedDataFieldKeys()
    {
        //FORMAT TO BE CHECKED
        //return str_replace(',', '\;', Mage::getStoreConfig('atos_api/' . $this->_method . '/data_field'));
        return '';//'CARD_NO_LOGO\;NO_COPYRIGHT\;';
        /*
         *  CARD_NO_LOGO\;NO_COPYRIGHT\;
         */
    }

    /**
     * Get Atos/Sips keywords data field
     *
     * @return array
     */
    public function getDataFieldKeys()
    {
        $types = array(
            "CARD_NO_LOGO" => "CARD_NO_LOGO",
            "NO_COPYRIGHT" => "NO_COPYRIGHT",
            "NO_DISPLAY_CARD" => "NO_DISPLAY_CARD",
            "NO_DISPLAY_CANCEL" => "NO_DISPLAY_CANCEL",
            "NO_SSL_SYMBOLS" => "NO_SSL_SYMBOLS",
            "NO_WINDOWS_MSG" => "NO_WINDOWS_MSG",
            "NO_DISPLAY_URL" => "NO_DISPLAY_URL",
            "NO_RESPONSE_PAGE" =>"NO_RESPONSE_PAGE"
        );
        /*
        foreach (Mage::getConfig()->getNode('global/payment/atos/data_field')->asArray() as $data) {
            $types[$data['code']] = $data['name'];
        }
        */

        return $types;
    }

    /**
     * Get binary request file path
     *
     * @return string
     */
    public function getBinRequest() {
        return $this->moduleDirReader->getModuleDir('','Cymit_Atos').'/view/res/atos_standard/bin/static/request';
    }

    /**
     * Get binary response file path
     *
     * @return string
     */
    public function getBinResponse() {
        return $this->moduleDirReader->getModuleDir('','Cymit_Atos').'/view/res/atos_standard/bin/static/response';
    }

    /**
     * Get if must check IP
     *
     * @return int
     */
    public function getCheckByIpAddress()
    {
        return 0;//(int) Mage::getStoreConfig('atos_api/' . $this->_method . '/check_ip_address');
    }

    /**
     * Get authorized IPs
     *
     * @return array
     */
    public function getAuthorizedIps()
    {
        return ["127.0.0.1"];//explode(',', Mage::getStoreConfig('atos_api/' . $this->_method . '/authorized_ips'));
    }

}
