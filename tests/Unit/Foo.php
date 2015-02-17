<?php

namespace Sabre\Katana\Test\Unit;

class Foo extends Suite
{
    public function case_foo()
    {
        $this
            ->integer(42)
                ->isEqualTo(42);
    }
}
