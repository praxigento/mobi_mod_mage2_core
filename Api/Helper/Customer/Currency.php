<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Core\Api\Helper\Customer;

/**
 * Convert base currency into/from customer currency.
 * Default implementation returns amount w/o changes.
 *
 * (Santegra project legacy, should not be used in other projects).
 */
interface Currency
{
    /**
     * @param float $amount
     * @param int|array|\Praxigento\Core\Data|null $customer ID or data object.
     * @return float
     */
    public function convertFromBase($amount, $customer = null);

    /**
     * Get customer's currency code.
     *
     * @param int|array|\Praxigento\Core\Data|null $customer ID or data object.
     * @return string
     */
    public function getCurrency($customer);

    /**
     * @param float $amount
     * @param int|array|\Praxigento\Core\Data|null $customer ID or data object.
     * @return float
     */
    public function convertToBase($amount, $customer = null);
}