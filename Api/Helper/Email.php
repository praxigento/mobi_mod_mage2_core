<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Core\Api\Helper;

/**
 * Replace recipients emails before sending (in some cases emails should be sent to the parents).
 */
interface Email
{
    /**
     * Process email address of the recipient & return valid email for the input:
     *
     * 790000145.__.sponsor@praxigento.com => sponsor@praxigento.com
     *
     * see \Praxigento\Core\Plugin\Magento\Framework\Mail\Message::aroundAddTo
     *
     * @param $email
     * @param $name
     * @return array [email, name]
     */
    public function validateRecipient($email, $name = '');
}