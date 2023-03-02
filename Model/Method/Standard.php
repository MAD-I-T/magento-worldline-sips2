<?php

namespace Madit\Sips2\Model\Method;

use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\Method\Logger;

class Standard extends \Madit\Sips2\Model\Method\AbstractMeans
{
    protected $_code = 'sips2_standard';
    protected $_formBlockType = 'Madit\Sips2\Block\Form\Standard';
    protected $_infoBlockType = 'Madit\Sips2\Block\Info\Standard';
    protected $_redirectBlockType = 'Madit\Sips2\Block\Redirect\Standard';

    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = true;
    protected $storeManager;
    protected $urlInterface;

    /**
     * Standard constructor.
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param \Madit\Sips2\Model\Config $config
     * @param \Madit\Sips2\Model\Api\Request $requestApi
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\Order $orderInterface
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        string $code,
        string $formBlockType,
        string $infoBlockType,
        \Madit\Sips2\Model\Config $config,
        \Madit\Sips2\Model\Api\Request $requestApi,
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
     * First call to the Sips2 server
     */
    public function callRequest()
    {
        $parameters = "";

        $binPath = "";

        $sipsVersion = $this->getConfig()->getConfigData("sips_version", "sips2_standard/default");

        $parameters = [
            'amount' => $this->_getAmount(), //Note that the amount entered in the "amount" field is in cents
            'automaticResponseUrl' => $this->_getAutomaticResponseUrl(),
            'currencyCode' => "978",
            'captureMode' => $this->_getCaptureMode(),
            'captureDay' => $this->_getCaptureDay(),
            'customerId' => $this->_getCustomerId(),
            'customerEmail' => $this->_getCustomerEmail(),
            'interfaceVersion' => "IR_WS_2.20",
            //"keyVersion" => $this->getConfig()->getConfigData("secret_key_version", "sips2_standard"),
            'merchantId' => $this->getConfig()->getMerchantId(),
            'normalReturnUrl' => $this->_getNormalReturnUrl(),
            'orderChannel' => "INTERNET",
            'orderId' => $this->_getOrderId(),

            /*
            //"secretKey" => $this->getConfig()->getConfigData("secret_key", "sips2_standard"),
            "transactionReference" => "",  // usefull for native WL Sips 2.0 merchantIds.
               Merchants migrating from WL Sips 1.0 will provide s10TransactionId instead
              "sealAlgorithm" => $this->getConfig()->getConfigData("seal_algorithm", "sips2_standard"),
               "paysage_json_url" => $this->getConfig()->getConfigData("paysage_json_url", "sips2_standard")
            */
        ];

        if ($sipsVersion == 2) {

            $parameters['s10TransactionReference'] =  [
                's10TransactionId' => substr($this->_getOrderId(), -6)
            ];
        } elseif ($sipsVersion == 3) {
            $curDate =  date('Ydm', time());
            $parameters['transactionReference'] = $curDate. substr($this->_getOrderId(), -6);
        } else {
            $parameters['transactionReference'] = "";
        }

        if ($this->getConfigData('debug')) {
            $this->debugRequest($parameters);
        }

        $sips = $this->getApiRequest()->doRequest($parameters, $binPath, $sipsVersion);

        //echo var_dump($sips, $sips['code'], $sipsVersion);
        //die(print_r($parameters));
        if (($sips['code'] === "") && ($sips['error'] === "")) {
            $this->_error = true;
            $this->_message = __(
                '<br /><center>Call request file error</center><br />Executable file request not found (%1)',
                $binPath
            );
        } elseif ($sips['code'] != 0) {
            $this->_error = true;
            $this->_message = __(
                '<br /><center>Call payment API error</center><br />Error message: %1',
                $sips['error']
            );
        } else {
            $this->_message = $sips['error'] . '<br />';
            if ($sipsVersion != 1) {
                $this->_response = $sips['output'];
            } else {
                $this->_response = $sips['message'];
            }
        }
    }

    /**
     * Get Payment Means
     *
     * @return string
     */
    protected function _getPaymentMeans()
    {
        return str_replace(',', ',2,', $this->getConfigData('cctypes')) . ',2';
        //return 'MASTERCARD,2,CB,2,VISA,2';
    }

    /*
    public function getConfigData($field, $storeId = null, $flag = false)
    {
        $path = 'payment/sips2_standard/default/' . $field;


        //echo "dedede".$path."ddede";
        //if($field === 'sisps_version')

        if (!$flag) {
            return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
    }
    */

    /**
     * Get normal return URL
     *
     * @return string
     */
    protected function _getNormalReturnUrl()
    {
        //return Mage::getUrl('sips2/payment_standard/normal', array('_secure' => true));
        return $this->urlInterface->getUrl('sips2madit/payment/normalcallback');
    }

    /**
     * Get cancel return URL
     *
     * @return string
     */
    protected function _getCancelReturnUrl()
    {
        //return Mage::getUrl('sips2/payment_standard/cancel', array('_secure' => true));
        return $this->urlInterface->getUrl('sips2madit/payment/cancel');
    }

    /**
     * Get automatic response URL
     *
     * @return string
     */
    protected function _getAutomaticResponseUrl()
    {
        // return Mage::getUrl('sips2/payment_standard/automatic', array('_secure' => true));
        return $this->urlInterface->getUrl('sips2madit/payment/autoresponse');
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        //return Mage::getUrl('sips2/payment_standard/redirect', array('_secure' => true));
        return $this->urlInterface->getUrl('sips2madit/payment/redirect');
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
