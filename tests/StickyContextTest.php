<?php

use Decahedron\StickyLogging\StickyContext;

class StickyContextTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass()
    {
        require __DIR__ .'/../vendor/autoload.php';
        parent::setUpBeforeClass();
    }

    public function test_it_can_execute_closure_values()
    {
        StickyContext::add('arya', function () {
            return 'Stark';
        });

        $this->assertEquals(['arya' => 'Stark'], StickyContext::all());
    }
}
