<?php

namespace Eventsourcerer\EventSourcererLaravel;

interface EventHandler
{
    public function handle(): callable;
}
