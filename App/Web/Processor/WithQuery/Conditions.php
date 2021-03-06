<?php

/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Core\App\Web\Processor\WithQuery;

/**
 * Process conditions from API request and add parts to SQL query.
 */
class Conditions
{
    /** @var \Praxigento\Core\App\Web\Processor\WithQuery\Conditions\Filter\Parser */
    private $subFilterParser;

    public function __construct(
        \Praxigento\Core\App\Web\Processor\WithQuery\Conditions\Filter\Parser $subFilterParser
    ) {
        $this->subFilterParser = $subFilterParser;
    }

    /**
     * @param \Magento\Framework\DB\Select $query
     * @param \Praxigento\Core\Api\App\Web\Request\Conditions $cond
     * @throws
     */
    public function exec($query, $cond)
    {
        /* perform action */
        $columns = $query->getPart(\Zend_Db_Select::COLUMNS); // get map [$tblAlias, $column, $valueAlias]
        if ($cond && $cond instanceof \Praxigento\Core\Api\App\Web\Request\Conditions) {
            /* we need to get reverse map: $valueAlias => [$tblAlias, $column] */
            $map = $this->mapReverse($columns);

            /* process filters */
            $filter = $cond->getFilter();
            if ($filter) {
                $sql = $this->subFilterParser->parse($filter, $map);
                if ($sql) $query->where($sql);
            }

            /* process limit & offset */
            $limit = (int)$cond->getLimit();
            $offset = (int)$cond->getOffset();
            if ($limit && $offset) {
                $query->limit($limit, $offset);
            } elseif ($limit) {
                $query->limit($limit);
            }

            /* process order */
            $order = $cond->getOrder();
            if (is_array($order)) {
                foreach ($order as $one) {
                    $alias = $one->getAttr();
                    $dir = $one->getDir();
                    if (strtoupper($dir) == \Zend_Db_Select::SQL_DESC) {
                        $dir = \Zend_Db_Select::SQL_DESC;
                    } else {
                        $dir = \Zend_Db_Select::SQL_ASC;
                    }
                    if (isset($map[$alias])) {
                        /** @var \Praxigento\Core\App\Web\Processor\WithQuery\Alias $data */
                        $data = $map[$alias];
                        $tblAlias = $data->getTable();
                        $col = $data->getField();
                        /* don't add quotes to names (`name`), Zend will do it. */
                        $query->order("$tblAlias.$col $dir");
                    }
                }
            }
        }
        return $query;
    }

    /**
     * Re-compose column information to [$valueAlias=>[$tableAlias, $column]] form.
     *
     * @param $columns
     * @return array
     * @throws \Exception
     */
    private function mapReverse($columns)
    {
        $result = [];
        foreach ($columns as $one) {
            $tableAlias = $one[0];
            $column = $one[1];
            $valueAlias = $one[2];
            $data = new \Praxigento\Core\App\Web\Processor\WithQuery\Alias();
            $data->setAlias($valueAlias);
            $data->setTable($tableAlias);
            $data->setField($column);
            $result[$valueAlias] = $data;
        }
        return $result;
    }
}