<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Core\Repo\Def;


/**
 * Default implementation for Data Source repository to do read-only operations with database. All methods throw exceptions.
 */
class BaseDataSource
    extends \Praxigento\Core\Repo\Def\Base
    implements \Praxigento\Core\Repo\IBaseDataSource
{
    /** @inheritdoc */
    public function get(
        $where = null,
        $order = null,
        $limit = null,
        $offset = null,
        $columns = null,
        $group = null,
        $having = null
    ) {
        /* override this method in the children classes */
        throw new \Exception('Method is not implemented yet.');
    }

    /** @inheritdoc */
    public function getById($id)
    {
        /* override this method in the children classes */
        throw new \Exception('Method is not implemented yet.');
    }

    /** @inheritdoc */
    public function getQueryToSelect()
    {
        /* override this method in the children classes */
        throw new \Exception('Method is not implemented yet.');
    }

    /** @inheritdoc */
    public function getQueryToSelectCount()
    {
        /* override this method in the children classes */
        throw new \Exception('Method is not implemented yet.');
    }

}