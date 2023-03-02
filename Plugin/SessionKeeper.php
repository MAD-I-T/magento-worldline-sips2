<?php

declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Madit\Sips2\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Session\SessionStartChecker;

/**
 * Intended to preserve session cookie after submitting POST form from PayPal to Magento controller.
 */
class SessionKeeper
{
    private const TRANSPARENT_REDIRECT_PATH = 'sips2madit/payment/normalcallback';

    /**
     * @var Http
     */
    private $request;

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Prevents session starting while instantiating PayPal transparent redirect controller.
     *
     * @param SessionStartChecker $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCheck(SessionStartChecker $subject, bool $result): bool
    {
        if ($result === false) {
            return false;
        }

        return strpos((string)$this->request->getPathInfo(), self::TRANSPARENT_REDIRECT_PATH) === false;
    }
}
