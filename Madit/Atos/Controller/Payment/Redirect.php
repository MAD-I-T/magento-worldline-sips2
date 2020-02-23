<?php
/**
 * Created by IntelliJ IDEA.
 * User: madalien
 * Date: 8/17/17
 * Time: 1:32 PM
 */

namespace Madit\Atos\Controller\Payment;

use Madit\Atos\Controller\Index\Index;

class Redirect extends Index
{

    /**
     * Dispatch request
     * When a customer chooses Atos/Sips Standard on Checkout/Payment page
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $this->getAtosSession()->setQuoteId($this->getCheckoutSession()->getLastRealOrder()->getQuoteId());
        $this->getCheckoutSession()->unsQuoteId();
        $this->getCheckoutSession()->unsRedirectUrl();
        return $resultPage;
    }
}
