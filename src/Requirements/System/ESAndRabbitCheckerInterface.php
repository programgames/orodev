<?php

namespace Programgames\OroDev\Requirements\System;

interface ESAndRabbitCheckerInterface
{
    public function checkEsRabbitMq(string $eSVersion, string $rabbitMqVersion);
}
