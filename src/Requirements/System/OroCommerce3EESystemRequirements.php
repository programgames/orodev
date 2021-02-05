<?php

namespace Programgames\OroDev\Requirements\System;

class OroCommerce3EESystemRequirements extends OroCommerce3CESystemRequirements implements ESAndRabbitCheckerInterface
{
    const ELASTIC_SEARCH_VERSION = "6.*";
    const RABBIT_MQ_VERSION = ">=3.6";

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
