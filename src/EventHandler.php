<?php

namespace PearTreeWeb\EventSourcerer\LaravelClient;

interface EventHandler
{
    public function handle(): callable;
}
