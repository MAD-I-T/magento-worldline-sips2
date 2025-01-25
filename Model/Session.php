<?php
namespace Madit\Sips2\Model;

class Session extends \Magento\Framework\Session\SessionManager
{

    protected $_quoteId;
    protected $_response;
    protected $_redirectMessage;
    protected $_redirectTitle;
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
    protected $scopeConfig;
    protected $ccType;
    protected $storeManager;

    /**
     * Session constructor.
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Adminhtml\System\Config\Source\Payment\Cctype $ccType
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Response\Http $response
     * @throws \Magento\Framework\Exception\SessionException
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Madit\Sips2\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Response\Http $response
    ) {
        $this->moduleDirReader = $moduleDirReader;
        $this->scopeConfig = $scopeConfig;
        $this->ccType = $ccType;
        $this->storeManager = $storeManager;

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
        $this->_eventManager->dispatch('sips2_session_init', ['sips2_session' => $this]);
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

    /**
     * Get quote id key
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getQuoteIdKey()
    {
        return 'quote_id_' . $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Set quote Id
     *
     * @param mixed $quoteId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setQuoteId($quoteId)
    {
        $this->setData($this->_getQuoteIdKey(), $quoteId);
    }

    /**
     * Get quote Id
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteId()
    {
        return $this->getData($this->_getQuoteIdKey());
    }

    /**
     * Get response key
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getResponseKey()
    {
        return 'response_' . $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Set response
     *
     * @param mixed $response
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setResponse($response)
    {
        $this->setData($this->_getResponseKey(), $response);
    }

    /**
     * Get response
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getResponse()
    {
        return $this->getData($this->_getResponseKey());
    }

    /**
     * Get Msg
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getRedirectMessageKey()
    {
        return 'redirect_message_' . $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Set Msg
     *
     * @param mixed $message
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setRedirectMessage($message)
    {
        $this->setData($this->_getRedirectMessageKey(), $message);
    }

    /**
     * Get redirect Msg
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRedirectMessage()
    {
        return $this->getData($this->_getRedirectMessageKey());
    }

    /**
     * Get Redirect Msg title
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getRedirectTitleKey()
    {
        return 'redirect_title_' . $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Set redirect Msg title
     *
     * @param mixed $title
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setRedirectTitle($title)
    {
        $this->setData($this->_getRedirectTitleKey(), $title);
    }

    /**
     * Get redirect Title
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRedirectTitle()
    {
        return $this->getData($this->_getRedirectTitleKey());
    }
}
