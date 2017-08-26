<?php
/**
 * Created by IntelliJ IDEA.
 * User: madalien
 * Date: 8/17/17
 * Time: 2:33 PM
 */
namespace Cymit\Atos\Controller\Payment;

use Cymit\Atos\Controller\Index\Index;
use Magento\Framework\View\Result\PageFactory;

class Failure extends Index
{

    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Cymit\Atos\Model\Api\Files $filesApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Cymit\Atos\Model\Adminhtml\System\Config\Source\Payment\Cctype $ccType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Cymit\Atos\Model\Config $config,
        \Cymit\Atos\Model\Api\Request $requestApi,
        \Cymit\Atos\Model\Api\Response $responseApi,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order $orderInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Cymit\Atos\Model\Session $atosSession,
        \Psr\Log\LoggerInterface $logger,
        \Cymit\Atos\Helper\Data $atosHelper,
        \Cymit\Atos\Model\Method\Standard $standardMethod,
        \Cymit\Atos\Model\Ipn $atosIpn,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        PageFactory  $resultPageFactory
    )
    {
        parent::__construct(
            $moduleDirReader,
            $filesApi,
            $scopeConfig,
            $ccType,
            $storeManager,
            $config,
            $requestApi,
            $responseApi,
            $checkoutSession,
            $quoteFactory,
            $orderInterface,
            $customerSession,
            $atosSession,
            $logger,
            $atosHelper,
            $standardMethod,
            $atosIpn,
            $context,
            $blockFactory,
            $resultPageFactory
        );
    }
    /**
     * Dispatch request
     * When Atos/Sips returns
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('atos_failure')->setTitle($this->getAtosSession()->getRedirectTitle());
        $resultPage->getLayout()->getBlock('atos_failure')->setMessage($this->getAtosSession()->getRedirectMessage());
        $this->getAtosSession()->unsetAll();
        return $resultPage;
    }

}