<?php

namespace Decahedron\StickyLogging\Laravel;

use Decahedron\StickyLogging\StickyContextProcessor;

class UseStickyLogging
{
    /**
     * @param \Monolog\Logger $logger
     */
    public function __invoke($logger)
    {
        $logger->pushProcessor(new StickyContextProcessor);
    }
}
