<?php
/**
 * Base class to create database schema installers.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Core\App\Setup\Schema;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

abstract class Base implements InstallSchemaInterface
{
    /** @var \Magento\Framework\App\ResourceConnection */
    protected $_resource;
    /** @var \Magento\Framework\DB\Adapter\AdapterInterface */
    protected $_conn;
    /** @var \Praxigento\Core\App\Setup\Dem\Tool */
    protected $_toolDem;
    /** @var  \Magento\Framework\Setup\ModuleContextInterface */
    protected $_context;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Praxigento\Core\App\Setup\Dem\Tool $toolDem

    ) {
        $this->_resource = $resource;
        $this->_conn = $resource->getConnection();
        $this->_toolDem = $toolDem;
    }

    /**
     * Module specific routines to create database structure on install.
     */
    protected abstract function _setup();

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->_context = $context;
        $setup->startSetup();
        /* perform module specific operations */
        $this->_setup();
        $setup->endSetup();
    }
}