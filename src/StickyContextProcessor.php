<?php

namespace Decahedron\StickyLogging;

class StickyContextProcessor
{
    public function __invoke($record)
    {
        if (StickyContext::isEmpty() || StickyContext::disabled()) {
            return $record;
        }

        $record['extra']['sticky'] = StickyContext::all();

        return $record;
    }
}