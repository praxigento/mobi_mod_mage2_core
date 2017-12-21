<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Core\App\Transaction\Database;

/**
 * Transaction items factory used by Database Transaction Manager.
 */
interface IFabrique
{
    /**
     * @param string $transactionName
     * @param string $connectionName
     * @return \Praxigento\Core\App\Transaction\Database\IItem
     */
    public function create($transactionName, $connectionName);

}