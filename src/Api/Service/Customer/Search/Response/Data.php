<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Core\Api\Service\Customer\Search\Response;

/**
 * Contains suggestions for customers found by key (name/email/mlm_id).
 *
 * (Define getters explicitly to use with Swagger tool)
 *
 */
class Data
    extends \Praxigento\Core\Data
{
    const ITEMS = 'items';

    /**
     * @return \Praxigento\Core\Api\Service\Customer\Search\Response\Data\Item[]
     */
    public function getItems()
    {
        $result = parent::get(self::ITEMS);
        return $result;
    }

    /**
     * @param \Praxigento\Core\Api\Service\Customer\Search\Response\Data\Item[] $data
     */
    public function setItems($data)
    {
        parent::set(self::ITEMS, $data);
    }
}