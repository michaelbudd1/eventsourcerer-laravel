<?php

namespace EventSourcerer\EventSourcererLaravel;

interface EventHandler
{
    public function handle(): callable;
}
