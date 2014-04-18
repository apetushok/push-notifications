<?php

namespace Push;

interface Service
{
    public function __construct($config);
    public function sendMessages($device_ids,$message);
}