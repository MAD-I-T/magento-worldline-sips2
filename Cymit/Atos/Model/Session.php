<?php
namespace Cymit\Atos\Model;


class Session extends  \Magento\Framework\Session\SessionManager
{

    protected $_quoteId;
    protected $_response;
    protected $_redirectMessage;
    protected $_redirectTitle;

    /**
     * Class constructor. Initialize Atos/Sips Standard session namespace
    public function __construct()
    {
        $this->init('atos');
    }

     */


    protected $_session;
    protected $_coreUrl = null;
    protected $_configShare;
    protected $_urlFactory;
    protected $_eventManager;
    protected $response;
    protected $_sessionManager;

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
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Response\Http $response
    ) {
        $this->moduleDirReader = $moduleDirReader;
        $this->filesApi = $filesApi;
        $this->scopeConfig = $scopeConfig;
        $this->ccType = $ccType;
        $this->storeManager = $storeManager;
        //$this->start();

        $this->_session = $session;
        $this->_eventManager = $eventManager;

        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
        $this->response = $response;
        $this->_eventManager->dispatch('atos_session_init', ['atos_session' => $this]);
    }
    /**
     * Unset all data associated with object
     */
    public function unsetAll()
    {
        parent::unsetAll();
        $this->clearStorage();
        $this->_quoteId = null;
        $this->_response = null;
        $this->_redirectMessage = null;
        $this->_redirectTitle = null;
    }

    protected function _getQuoteIdKey()
    {
        return 'quote_id_' . $this->storeManager->getStore()->getWebsiteId();
    }

    public function setQuoteId($quoteId)
    {
        $this->setData($this->_getQuoteIdKey(), $quoteId);
    }

    public function getQuoteId()
    {
        return $this->getData($this->_getQuoteIdKey());
    }

    protected function _getResponseKey()
    {
        return 'response_' . $this->storeManager->getStore()->getWebsiteId();
    }

    public function setResponse($response)
    {
        $this->setData($this->_getResponseKey(), $response);
    }

    public function getResponse()
    {
        return $this->getData($this->_getResponseKey());
    }

    protected function _getRedirectMessageKey()
    {
        return 'redirect_message_' . $this->storeManager->getStore()->getWebsiteId();
    }

    public function setRedirectMessage($message)
    {
        $this->setData($this->_getRedirectMessageKey(), $message);
    }

    public function getRedirectMessage()
    {
        return $this->getData($this->_getRedirectMessageKey());
    }

    protected function _getRedirectTitleKey()
    {
        return 'redirect_title_' . $this->storeManager->getStore()->getWebsiteId();
    }

    public function setRedirectTitle($title)
    {
        $this->setData($this->_getRedirectTitleKey(), $title);
    }

    public function getRedirectTitle()
    {
        return $this->getData($this->_getRedirectTitleKey());
    }

}
