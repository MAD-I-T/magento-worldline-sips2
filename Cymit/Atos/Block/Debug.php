<?php
namespace Cymit\Atos\Block;

class Debug extends \Magento\Framework\View\Element\AbstractBlock
{


    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * Debug constructor.
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
    }

    protected function _toHtml()
    {
        $response = $this->getData();

        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $html .= '<html>';
        $html .= '<head></head>';
        $html .= '<body>';

        $html .= $response['hash']['error'];

        $html .= '<table width="700" cellpadding="3" style="BORDER-RIGHT: #000000 1px solid; BORDER-TOP: #000000 1px solid; FONT-SIZE: 75%;  BORDER-LEFT: #000000 1px solid; BORDER-BOTTOM: #000000 1px solid; font-family: sans-serif; border-collapse: collapse;">';
        $html .= '<tbody><tr style="background-color: #9999cc"><td align="center">';
        $html .= '<b>' . $this->__('Sips Server Response') . '</b>';
        $html .= '</td></tr><tr></tr>';

        foreach ($response['hash'] as $key => $value)
            if ($key != 'error')
                $html .= "<tr><td>$key ($value)</td></tr>";

        $html .= '</tbody></table>';
        $html .= '<center><h3><a href="' . $this->urlInterface->getUrl($response['redirect_url'], array('_secure' => true)) . '">' . $this->__('Click here to return to %1', Mage::app()->getWebsite()->getName()) . '</a></h3></center><br />';
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }

}
