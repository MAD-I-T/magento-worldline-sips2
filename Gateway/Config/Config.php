<?php


namespace Madit\Sips2\Gateway\Config;

use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const PAYMENT_ACTION_CAPTURE = 'AUTHOR_CAPTURE';
    const PAYMENT_ACTION_AUTHORIZE = 'VALIDATION';

    protected $_method;
    protected $_merchantId;
    protected $scopeConfig;
    protected $ccType;
    protected $serializer;
    protected $storeManager;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param \Madit\Sips2\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null $methodCode
     * @param string $pathPattern
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Madit\Sips2\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN,
        Json $serializer = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->ccType = $ccType;
        $this->storeManager = $storeManager;
        $this->_merchantId = $this->getConfigData('merchant_id');
        //parent::__construct($data);

        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);

        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /* @return \Madit\Sips2\Model\Config */
    public function initMethod($method)
    {
        if (empty($this->_method)) {
            $this->_method = $method;
        }
        return $this;
    }

    /**
     * Mapper from Sips2/Sips Standard payment actions to Magento payment actions
     *
     * @return string|null
     */
    public function getPaymentAction($action)
    {
        switch ($action) {
            case MethodInterface::ACTION_AUTHORIZE:
                return self::PAYMENT_ACTION_AUTHORIZE;
            case MethodInterface::ACTION_AUTHORIZE_CAPTURE:
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
        $paymentActions = [
            MethodInterface::ACTION_AUTHORIZE_CAPTURE => __('Author Capture'),
            MethodInterface::ACTION_AUTHORIZE => __('Validation')
        ];
        return $paymentActions;
    }


    /**
     * Get merchant ID
     *
     * @return string
     */
    public function getMerchantId()
    {
        return empty($this->_merchantId) ? '014295303911111' : $this->_merchantId;
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
        $sips2ConfigCountries = $this->getMerchantCountries();

        if (count($sips2ConfigCountries) === 1) {
            return strtolower($sips2ConfigCountries[0]);
        }

        if (array_key_exists($currentCountryCode, $sips2ConfigCountries)) {
            $code = array_keys($sips2ConfigCountries);
            $key = array_search($currentCountryCode, $code);

            return strtolower($code[$key]);
        }

        return 'fr';
    }

    /**
     * Get Sips2/Sips authorized countries
     *
     * @return array
     */
    public function getMerchantCountries()
    {
        /*
        $countries = array();
        foreach (Mage::getConfig()->getNode('global/payment/sips2/merchant_country')->asArray() as $data) {
            $countries[$data['code']] = $data['name'];
        }
        */

        return ["fr"=>"France", "es" =>"Espagne"];
    }

    /**
     * Get currency code
     *
     * @return string|boolean
     */
    public function getCurrencyCode($currentCurrencyCode)
    {
        $sips2ConfigCurrencies = $this->getCurrencies();

        if (array_key_exists($currentCurrencyCode, $sips2ConfigCurrencies)) {
            return $sips2ConfigCurrencies[$currentCurrencyCode];
        } else {
            return false;
        }
    }

    /**
     * Get Sips2/Sips authorized currencies
     *
     * @return array
     */
    public function getCurrencies()
    {
        $currencies = [
            "EUR" =>"978"
        ];
        /*
        foreach (Mage::getConfig()->getNode('global/payment/sips2/currencies')->asArray() as $data) {
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
        $sips2ConfigLanguages = $this->getLanguages();

        if (count($sips2ConfigLanguages) === 1) {
            return strtolower($sips2ConfigLanguages[0]);
        }

        if (array_key_exists($language, $sips2ConfigLanguages)) {
            $code = array_keys($sips2ConfigLanguages);
            $key = array_search($language, $code);

            return strtolower($code[$key]);
        }

        return 'fr';
    }

    /**
     * Get Sips2/Sips authorized languages
     *
     * @return array
     */
    public function getLanguages()
    {
        $languages = [
            "fr" => "Français",
            "en" => "Anglais"
        ];
        /*
        foreach (Mage::getConfig()->getNode('global/payment/sips2/languages')->asArray() as $data) {
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
        //return str_replace(',', '\;', Mage::getStoreConfig('sips2_api/' . $this->_method . '/data_field'));
        return '';//'CARD_NO_LOGO\;NO_COPYRIGHT\;';
        /*
         *  CARD_NO_LOGO\;NO_COPYRIGHT\;
         */
    }

    /**
     * Get Worldline Seal algorithm
     *
     * @return array
     */
    public function getSealAlgorithmOptions(): array
    {
        return [
            "HMAC-SHA-256" => "HMAC-SHA-256",
            "SHA-256" => "SHA-256"
        ];
    }

    /**
     * Get Worldline SIPS version
     *
     * @return array
     */
    public function getSIPSVersionOptions(): array
    {
        return [
            //1 => "SIPS 1.0",
            2 => "SIPS 2.0 (migration)",
            3 => "SIPS 2.0 (transactionReference)",
            4 => "SIPS 2.0 (auto)"
        ];
    }

    /**
     * Get Sips2/Sips keywords data field
     *
     * @return array
     */
    public function getDataFieldKeys()
    {
        $types = [
            "CARD_NO_LOGO" => "CARD_NO_LOGO",
            "NO_COPYRIGHT" => "NO_COPYRIGHT",
            "NO_DISPLAY_CARD" => "NO_DISPLAY_CARD",
            "NO_DISPLAY_CANCEL" => "NO_DISPLAY_CANCEL",
            "NO_SSL_SYMBOLS" => "NO_SSL_SYMBOLS",
            "NO_WINDOWS_MSG" => "NO_WINDOWS_MSG",
            "NO_DISPLAY_URL" => "NO_DISPLAY_URL",
            "NO_RESPONSE_PAGE" =>"NO_RESPONSE_PAGE"
        ];
        /*
        foreach (Mage::getConfig()->getNode('global/payment/sips2/data_field')->asArray() as $data) {
            $types[$data['code']] = $data['name'];
        }
        */

        return $types;
    }


    /**
     * Get if must check IP
     *
     * @return int
     */
    public function getCheckByIpAddress()
    {
        return 0;//(int) Mage::getStoreConfig('sips2_api/' . $this->_method . '/check_ip_address');
    }

    /**
     * Get authorized IPs
     *
     * @return array
     */
    public function getAuthorizedIps()
    {
        return ["127.0.0.1"];//explode(',', Mage::getStoreConfig('sips2_api/' . $this->_method . '/authorized_ips'));
    }

    public function getConfigData($field, $paymentMethodCode = 'sips2_standard/default', $storeId = null, $flag = false)
    {
        $path = 'payment/' . $paymentMethodCode . '/' . $field;

        //if($field === 'sisps_version')
        //echo $path;

        if (!$flag) {
            return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
    }


    public function getValue($field, $storeId = null)
    {
        $path = 'payment/sips2_standard/default/' . $field;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
