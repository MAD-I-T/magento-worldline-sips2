<?php
/**
 * Created by IntelliJ IDEA.
 * User: madalien
 * Date: 8/17/17
 * Time: 2:33 PM
 */
namespace Madit\Sips2\Controller\Payment;

use Madit\Sips2\Controller\Index\Index;
use Magento\Framework\View\Result\PageFactory;

class Failure extends Index
{
    /**
     * Failure constructor.
     * @param \Magento\Framework\Module\Dir\Reader $moduleDirReader
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Madit\Sips2\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Madit\Sips2\Model\Config $config
     * @param \Madit\Sips2\Model\Api\Request $requestApi
     * @param \Madit\Sips2\Model\Api\Response $responseApi
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Sales\Model\Order $orderInterface
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Madit\Sips2\Model\Session $sips2Session
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Madit\Sips2\Helper\Data $sips2Helper
     * @param \Madit\Sips2\Model\Method\Standard $standardMethod
     * @param \Madit\Sips2\Model\Ipn $sips2Ipn
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Madit\Sips2\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Madit\Sips2\Model\Config $config,
        \Madit\Sips2\Model\Api\Request $requestApi,
        \Madit\Sips2\Model\Api\Response $responseApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Madit\Sips2\Model\Session $sips2Session,
        \Psr\Log\LoggerInterface $logger,
        \Madit\Sips2\Helper\Data $sips2Helper,
        \Madit\Sips2\Model\Method\Standard $standardMethod,
        \Madit\Sips2\Model\Ipn $sips2Ipn,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        PageFactory  $resultPageFactory
    ) {
        parent::__construct(
            $moduleDirReader,
            $scopeConfig,
            $ccType,
            $storeManager,
            $config,
            $requestApi,
            $responseApi,
            $checkoutSession,
            $quoteFactory,
            $quoteRepository,
            $orderInterface,
            $customerSession,
            $sips2Session,
            $logger,
            $sips2Helper,
            $standardMethod,
            $sips2Ipn,
            $context,
            $blockFactory,
            $resultPageFactory
        );
    }
    /**
     * Dispatch request
     *
     * When Sips2/Sips returns
     *
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('sips2_failure')->setTitle($this->getSips2Session()->getRedirectTitle());
        $resultPage->getLayout()->getBlock('sips2_failure')->setMessage($this->getSips2Session()->getRedirectMessage());
        $this->getSips2Session()->unsetAll();
        return $resultPage;
    }
}
