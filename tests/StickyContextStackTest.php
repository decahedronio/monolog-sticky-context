<?php

use Decahedron\StickyLogging\StickyContextStack;

class StickyContextStackTest extends \PHPUnit\Framework\TestCase
{
    public function test_it_can_add_sticky_data()
    {
        $stack = new StickyContextStack;

        $stack->add('username', 'aryastark');
        $this->assertEquals(['username' => 'aryastark'], $stack->all());
    }

    public function test_it_can_add_sticky_closure_data()
    {
        $stack = new StickyContextStack;

        $stack->add('username', function () {
            return 'aryastark';
        });

        $this->assertEquals(['username' => 'aryastark'], $stack->all());
    }

    public function test_it_can_flush_data()
    {
        $stack = new StickyContextStack;

        $stack->add('username', 'aryastark');
        $stack->flush();

        $this->assertEquals([], $stack->all());
    }
}
