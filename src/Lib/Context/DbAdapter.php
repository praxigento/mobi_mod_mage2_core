<?php
/**
 * Wrapper for version specific implementation of the common interface:
 *  - Mage_Core_Model_Resource
 *  - Magento\Framework\App\ResourceConnection
 *
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Core\Lib\Context;


use Praxigento\Core\Config as Cfg;
use Praxigento\Core\Lib\Context as Ctx;

/**
 * TODO: move implementation to 'Def' folder.
 */
class DbAdapter implements IDbAdapter, ITransactionManager
{

    /**
     * Current Transaction Level
     *
     * @var int
     */
    protected $_transactionLevel = 0;

    /** @var  \Magento_Db_Adapter_Pdo_Mysql|\Magento\Framework\DB\Adapter\Pdo\Mysql */
    protected $_defaultConnection;
    /** @var  \Mage_Core_Model_Resource|\Magento\Framework\App\ResourceConnection */
    protected $_resource;
    /** @var  IObjectManager */
    protected $_manObject;

    /**
     * Resource constructor.
     *
     * Don't set type for $m1Resource, it crashes the M2 ObjectManager (there is no \Mage_Core_Model_Resource)
     *
     * @param \Mage_Core_Model_Resource|\Magento\Framework\App\ResourceConnection|null $resource
     */
    public function __construct($resource = null)
    {
        // @codeCoverageIgnoreStart
        $this->_manObject = Ctx::instance()->getObjectManager();
        if (is_null($resource)) {
            if (class_exists('\Magento\Framework\App\ResourceConnection', false)) {
                $this->_resource = $this->_manObject->get(\Magento\Framework\App\ResourceConnection::class);
                $this->_defaultConnection = $this->_resource->getConnection(Cfg::DEFAULT_WRITE_RESOURCE);
            } elseif (class_exists('\Mage_Core_Model_Resource', false)) {
                $this->_resource = $this->_manObject->get(\Mage_Core_Model_Resource::class);
                $this->_defaultConnection = $this->_resource->getConnection(Cfg::DEFAULT_WRITE_RESOURCE);
            }
        } else {
            $this->_resource = $resource;
            $this->_defaultConnection = $this->_resource->getConnection(Cfg::DEFAULT_WRITE_RESOURCE);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get default database connector (core_write).
     *
     * @return \Magento_Db_Adapter_Pdo_Mysql|\Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    public function getDefaultConnection()
    {
        return $this->_defaultConnection;
    }

    /**
     * @return  \Mage_Core_Model_Resource|\Magento\Framework\App\ResourceConnection
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * @param $entityName M2 entity name
     *
     * @return string M1 or M2 table name (with prefix) for given entity name.
     */
    public function getTableName($entityName)
    {
        $mapped = Ctx::getMappedEntityName($entityName);
        $result = $this->_resource->getTableName($mapped);
        return $result;
    }

    public function getTransactionManager()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function transactionBegin()
    {
        $this->_transactionLevel++;
        $this->_defaultConnection->beginTransaction();
        $result = $this->_manObject->create(Ctx\Def\TransactionDefinition::class);
        $result->setLevel($this->_transactionLevel);
        return $result;
    }

    public function transactionClose(ITransactionDefinition $data)
    {
        $level = $data->getLevel();
        $levelUp = $level - 1;
        if ($level == $this->_transactionLevel) {
            $this->_defaultConnection->rollBack();
            $this->_transactionLevel--;
        } elseif ($levelUp) {
            // do nothing, this is committed transaction
        } else {
            // exception should be thrown but I suppose it will be done in the other place.
        }
    }

    public function transactionCommit(ITransactionDefinition $data)
    {
        $level = $data->getLevel();
        if ($level == $this->_transactionLevel) {
            $this->_defaultConnection->commit();
            $this->_transactionLevel--;
        }
    }

    public function transactionRollback(ITransactionDefinition $data)
    {
        $level = $data->getLevel();
        if ($level == $this->_transactionLevel) {
            $this->_defaultConnection->rollBack();
            $this->_transactionLevel--;
        }
    }
}