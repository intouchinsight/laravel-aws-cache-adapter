<?php

declare(strict_types=1);

namespace LukeWaite\LaravelAwsCacheAdapter\Tests;

use LukeWaite\LaravelAwsCacheAdapter\LaravelCacheAdapter;
use Mockery as m;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LaravelCacheAdapterTest extends TestCase
{
    /** @var \Mockery\MockInterface */
    protected $manager;

    protected $repository;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp(): void
    {
        $this->manager = m::mock('Illuminate\Cache\CacheManager');
        $this->manager->shouldReceive('store')
            ->with('file')
            ->once()
            ->andReturn($this->repository = m::mock('StdClass'));
    }

    #[Test]
    public function test_get_with_prefix()
    {
        $this->repository->shouldReceive('get')->with('aws_credentials_testkey')->once()->andReturn('testValue');

        $adapter = new LaravelCacheAdapter($this->manager, 'file', 'test');
        $this->assertEquals('testValue', $adapter->get('key'));
    }

    #[Test]
    public function test_remove_without_prefix()
    {
        $this->repository->shouldReceive('forget')->with('aws_credentials_key_to_remove')->once();

        $adapter = new LaravelCacheAdapter($this->manager, 'file', '');
        $adapter->remove('key_to_remove');
        $this->assertTrue(true); // Add assertion to avoid risky test
    }

    #[Test]
    public function test_set_less_than60_seconds_rounds_up()
    {
        $this->repository->shouldReceive('put')->with('aws_credentials_key', 'value', 1)->once();

        $adapter = new LaravelCacheAdapter($this->manager, 'file', '');
        $adapter->set('key', 'value', 59);
        $this->assertTrue(true); // Add assertion to avoid risky test
    }

    #[Test]
    public function test_set_greater_than60_seconds_rounds_down()
    {
        $this->repository->shouldReceive('put')->with('aws_credentials_key', 'value', 1)->once();

        $adapter = new LaravelCacheAdapter($this->manager, 'file', '');
        $adapter->set('key', 'value', 61);
        $this->assertTrue(true); // Add assertion to avoid risky test
    }

    #[Test]
    public function test_set_greater_than120_seconds_rounds_down()
    {
        $this->repository->shouldReceive('put')->with('aws_credentials_key', 'value', 2)->once();

        $adapter = new LaravelCacheAdapter($this->manager, 'file', '');
        $adapter->set('key', 'value', 121);
        $this->assertTrue(true); // Add assertion to avoid risky test
    }

    #[Test]
    public function test_set0_retains0()
    {
        $this->repository->shouldReceive('put')->with('aws_credentials_key', 'value', 0)->once();

        $adapter = new LaravelCacheAdapter($this->manager, 'file', '');
        $adapter->set('key', 'value', 0);
        $this->assertTrue(true); // Add assertion to avoid risky test
    }
}
