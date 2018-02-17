<?php

use Decahedron\StickyLogging\StickyContext;
use Decahedron\StickyLogging\StickyContextStack;

class StickyContextTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass()
    {
        require __DIR__ .'/../vendor/autoload.php';
        parent::setUpBeforeClass();
    }

    public function tearDown()
    {
        StickyContext::flush();
        StickyContext::defaultStack('sticky');

        parent::tearDown();
    }

    public function test_it_will_log_to_the_default_stack_if_none_is_specified()
    {
        StickyContext::add('username', 'aryastark');

        $this->assertEquals([
            'sticky' => ['username' => 'aryastark'],
        ], StickyContext::all());
    }

    public function test_it_can_retrieve_a_stack()
    {
        $this->assertInstanceOf(StickyContextStack::class, StickyContext::stack('vendor'));
    }

    public function test_it_can_use_multiple_stacks()
    {
        StickyContext::stack('primary')->add('user', function () {
            return 3;
        });
        StickyContext::stack('vendor')->add('request_id', '77df1440-d7c1-437d-9287-9cd7f5c43ec8');

        $this->assertEquals([
            'primary' => ['user' => 3],
            'vendor' => ['request_id' => '77df1440-d7c1-437d-9287-9cd7f5c43ec8']
        ], StickyContext::all());
    }

    public function test_it_can_use_a_different_default_stack()
    {
        StickyContext::defaultStack('primary');

        StickyContext::add('email', 'arya@housestark.co');
        $this->assertEquals([
            'primary' => ['email' => 'arya@housestark.co'],
        ], StickyContext::all());
    }

    public function test_it_moves_all_default_stack_data_to_the_new_key_when_the_default_stack_changes()
    {
        StickyContext::add('username', 'aryastark');
        $this->assertEquals([
            'sticky' => ['username' => 'aryastark']
        ], StickyContext::all());
        StickyContext::defaultStack('primary');
        StickyContext::add('email', 'arya@housestark.co');
        $this->assertEquals([
            'primary' => [
                'username' => 'aryastark',
                'email' => 'arya@housestark.co',
            ],
        ], StickyContext::all());
    }

    public function test_it_can_flush_all_stacks()
    {
        StickyContext::add('username', 'aryastark');
        StickyContext::stack('secondary')->add('username', 'robstark');

        StickyContext::flush();

        $this->assertEquals([], StickyContext::all());
    }

    public function test_it_can_flush_a_specific_stack()
    {
        StickyContext::add('username', 'aryastark');
        StickyContext::stack('secondary')->add('username', 'robstark');

        StickyContext::flush('secondary');

        $this->assertEquals([
            'sticky' => ['username' => 'aryastark'],
        ], StickyContext::all());
    }

    public function test_it_excludes_empty_stacks_from_the_result()
    {
        StickyContext::add('username', 'aryastark');
        StickyContext::stack('secondary');

        $this->assertEquals([
            'sticky' => ['username' => 'aryastark'],
        ], StickyContext::all());
    }
}
