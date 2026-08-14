<?php

namespace Pop\Test\TestAsset;

use Pop\Console\Command\AbstractCommand;

class TestCommand extends AbstractCommand
{

    public function handle()
    {
        echo $this->hasApplication() ? 'command-with-app' : 'command-no-app';
    }

}
