<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Core\Repo;

use Flancer32\Lib\DataObject;

/**
 * Basic interface for data sources repository (read-only mode).
 */
interface IBaseDataSource
{

    /**
     * Generic method to get data from repository.
     *
     * @param null $where
     * @param null $order
     * @param null $limit
     * @param null $offset
     * @param null $columns
     * @param null $group
     * @param null $having
     * @return array Found data or empty array if no data found.
     */
    public function get(
        $where = null,
        $order = null,
        $limit = null,
        $offset = null,
        $columns = null,
        $group = null,
        $having = null
    );

    /**
     * Get the data instance by ID (ID can be an array for complex primary keys).
     *
     * @param int|string|array $id
     * @return DataObject|array|bool Found instance data or 'false'
     */
    public function getById($id);

    /**
     * Retrieve connection to resource specified by $name
     *
     * @param string $name
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection($name);

    /**
     * Compose SELECT query for the simple entity or aggregate.
     *
     * @return ISelect
     */
    public function getQueryToSelect();

    /**
     * Compose COUNT SELECT query for the simple entity or aggregate.
     *
     * @return ISelect
     */
    public function getQueryToSelectCount();

}