<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Core\Test\BaseCase;

use Mockery as m;

/**
 * Base class to create units to test schema install classes base on \Praxigento\Core\Setup\Schema\Base.
 */
abstract class InstallSchema
    extends \Praxigento\Core\Test\BaseMockeryCase
{
    /** @var  \Mockery\MockInterface */
    protected $mConn;
    /** @var  \Mockery\MockInterface */
    protected $mContext;
    /** @var  \Mockery\MockInterface */
    protected $mResource;
    /** @var  \Mockery\MockInterface */
    protected $mSetup;
    /** @var  \Mockery\MockInterface */
    protected $mToolDem;

    protected function setUp()
    {
        parent::setUp();
        /** create mocks */
        $this->mConn = m::mock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->mContext = m::mock(\Magento\Framework\Setup\ModuleContextInterface::class);
        $this->mResource = m::mock(\Magento\Framework\App\ResourceConnection::class);
        $this->mSetup = m::mock(\Magento\Framework\Setup\SchemaSetupInterface::class);
        $this->mToolDem = m::mock(\Praxigento\Core\Setup\Dem\Tool::class);
        /** setup mocks for constructor */
        $this->mResource
            ->shouldReceive('getConnection')->once()
            ->andReturn($this->mConn);
    }

}