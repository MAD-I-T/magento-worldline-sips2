<?php

namespace Madit\Atos\Model\Method;

use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\Method\Logger;

class Standard extends \Madit\Atos\Model\Method\AbstractMeans
{
    protected $_code = 'atos_standard';
    protected $_formBlockType = 'Madit\Atos\Block\Form\Standard';
    protected $_infoBlockType = 'Madit\Atos\Block\Info\Standard';
    protected $_redirectBlockType = 'Madit\Atos\Block\Redirect\Standard';

    /**
     * Payment Method features
     * @var bool
     */
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = true;
    protected $storeManager;
    protected $urlInterface;

    /**
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param CommandPoolInterface|null $commandPool
     * @param ValidatorPoolInterface|null $validatorPool
     * @param \Magento\Payment\Gateway\Command\CommandManagerInterface|null $commandExecutor
     * @param \Madit\Atos\Model\Config $config
     * @param \Madit\Atos\Model\Api\Request $requestApi
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\Order $orderInterface
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param array $data
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        string $code,
        string $formBlockType,
        string $infoBlockType,
        \Madit\Atos\Model\Config $config,
        \Madit\Atos\Model\Api\Request $requestApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Payment\Model\Method\Logger $logger,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;

        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $config,
            $requestApi,
            $checkoutSession,
            $quoteFactory,
            $orderInterface,
            $logger,
            $data
        );

        /*
        parent::__construct(
            $config,
            $requestApi,
            $checkoutSession,
            $quoteFactory,
            $orderInterface,
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        */
    }

    /**
     * First call to the Atos server
     */
    public function callRequest()
    {
        // Affectation des paramètres obligatoires
        $parameters = "merchant_id=" . $this->getConfig()->getMerchantId();
        $parameters .= " merchant_country=" . $this->getConfig()->getMerchantCountry();
        $parameters .= " amount=" . $this->_getAmount();
        $parameters .= " currency_code=" . $this->getConfig()->getCurrencyCode($this->_getQuote()->getQuoteCurrencyCode());

        // Initialisation du chemin du fichier pathfile
        $parameters .= " pathfile=" . $this->getConfig()->getPathfile();

        // Affectation dynamique des autres paramètres
        $parameters .= " normal_return_url=" . $this->_getNormalReturnUrl();
        $parameters .= " cancel_return_url=" . $this->_getCancelReturnUrl();
        $parameters .= " automatic_response_url=" . $this->_getAutomaticResponseUrl();
        $parameters .= " language=" . $this->getConfig()->getLanguageCode();
        $parameters .= " payment_means=" . $this->_getPaymentMeans();

        if ($this->_getCaptureDay() > 0) {
            $parameters .= " capture_day=" . $this->_getCaptureDay();
        }

        $parameters .= " capture_mode=" . $this->_getCaptureMode();
        $parameters .= " customer_id=" . $this->_getCustomerId();
        $parameters .= " customer_email=" . $this->_getCustomerEmail();
        $parameters .= " customer_ip_address=" . $this->_getCustomerIpAddress();
        $parameters .= " data=" . str_replace(',', '\;', $this->getConfig()->getSelectedDataFieldKeys());
        $parameters .= " order_id=" . $this->_getOrderId();

        // Initialisation du chemin de l'executable request
        $binPath = $this->getConfig()->getBinRequest();

        // Debug
        if ($this->getConfigData('debug')) {
            $this->debugRequest($parameters);
        }

        $sips = $this->getApiRequest()->doRequest($parameters, $binPath);

        //echo var_dump($sips, $sips['code']);
        //die(print_r($parameters));
        if (($sips['code'] == "") && ($sips['error'] == "")) {
            $this->_error = true;
            $this->_message = __('<br /><center>Call request file error</center><br />Executable file request not found (%1)', $binPath);
        } elseif ($sips['code'] != 0) {
            $this->_error = true;
            $this->_message = __('<br /><center>Call payment API error</center><br />Error message: %1', $sips['error']);
        } else {
            // Active debug
            $this->_message = $sips['error'] . '<br />';
            $this->_response = $sips['message'];
        }
    }

    /**
     * Get Payment Means
     *
     * @return string
     */
    protected function _getPaymentMeans()
    {
        //return str_replace(',', ',2,', $this->getConfigData('cctypes')) . ',2';
        return 'MASTERCARD,2,CB,2,VISA,2';
    }

    /**
     * Get normal return URL
     *
     * @return string
     */
    protected function _getNormalReturnUrl()
    {
        //return Mage::getUrl('atos/payment_standard/normal', array('_secure' => true));
        return $this->urlInterface->getUrl('sherlock/payment/normalcallback');
    }

    /**
     * Get cancel return URL
     *
     * @return string
     */
    protected function _getCancelReturnUrl()
    {
        //return Mage::getUrl('atos/payment_standard/cancel', array('_secure' => true));
        return $this->urlInterface->getUrl('sherlock/payment/cancel');
    }

    /**
     * Get automatic response URL
     *
     * @return string
     */
    protected function _getAutomaticResponseUrl()
    {
        // return Mage::getUrl('atos/payment_standard/automatic', array('_secure' => true));
        return $this->urlInterface->getUrl('sherlock/payment/autoresponse');
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        //return Mage::getUrl('atos/payment_standard/redirect', array('_secure' => true));
        return $this->urlInterface->getUrl('sherlock/payment/redirect');
    }

    /**
     * Get capture day
     *
     * @return int
     */
    protected function _getCaptureDay()
    {
        return (int) $this->getConfigData('capture_day');
    }

    /**
     * Get capture mode
     *
     * @return string
     */
    protected function _getCaptureMode()
    {
        return $this->getConfig()->getPaymentAction($this->getConfigData('payment_action'));
    }
}
