<?php

namespace Decahedron\StickyLogging;

class StickyContextProcessor
{
    public function __invoke($record)
    {
        if (StickyContext::isEmpty() || StickyContext::disabled()) {
            return $record;
        }

        $record['extra'] = array_merge($record['extra'], StickyContext::all());

        return $record;
    }
}