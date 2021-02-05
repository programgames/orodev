<?php

namespace Programgames\OroDev\Requirements\System;

class OroPlatform4EESystemRequirements extends OroPlatform4CESystemRequirements implements ESAndRabbitCheckerInterface
{
    const ELASTIC_SEARCH_VERSION = "7.*";
    const RABBIT_MQ_VERSION = ">=3.7.21";

    use ESRabbitCheckerTrait;

    /**
     * OroDevRequirements constructor.
     * @param string $env
     */
    public function __construct($env = 'prod')
    {
        parent::__construct($env);

        $this->checkEsRabbitMq(self::ELASTIC_SEARCH_VERSION, self::RABBIT_MQ_VERSION);
    }
}
