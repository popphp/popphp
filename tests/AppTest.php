<?php

namespace Pop\Test;

use Pop\App;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{

    public function testConfig()
    {
        $this->assertNull(App::config());
    }

}
