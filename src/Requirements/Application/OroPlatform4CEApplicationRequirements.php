<?php

namespace Programgames\OroDev\Requirements\Application;

use Exception;
use Programgames\OroDev\Requirements\OroRequirement;
use Symfony\Requirements\PhpConfigRequirement;

/**
 * This class specifies all requirements and optional recommendations that are necessary to run the Oro Application.
 */
class OroPlatform4CEApplicationRequirements extends OroRequirements implements OroApplicationRequirementsInterface
{
    const REQUIRED_PHP_VERSION = '7.3.13';
    const REQUIRED_GD_VERSION = '2.0';
    const REQUIRED_CURL_VERSION = '7.0';
    const REQUIRED_NODEJS_VERSION = '>=12.0';

    /**
     * @param string $env
     * @throws Exception
     */
    public function __construct($env = 'prod')
    {
        parent::__construct(
            self::REQUIRED_PHP_VERSION,
            self::REQUIRED_GD_VERSION,
            self::REQUIRED_CURL_VERSION,
            self::REQUIRED_NODEJS_VERSION,
            $env
        );
    }

    /**
     * Get the list of mandatory requirements (all requirements excluding PhpIniRequirement)
     *
     * @return array
     */
    public function getMandatoryRequirements(): array
    {
        return array_filter(
            $this->getRequirements(),
            function ($requirement) {
                return !($requirement instanceof PhpConfigRequirement)
                    && !($requirement instanceof OroRequirement);
            }
        );
    }

    /**
     * Get the list of PHP ini requirements
     *
     * @return array
     */
    public function getPhpConfigRequirements(): array
    {
        return array_filter(
            $this->getRequirements(),
            function ($requirement) {
                return $requirement instanceof PhpConfigRequirement;
            }
        );
    }

    /**
     * Get the list of Oro specific requirements
     *
     * @return array
     */
    public function getOroRequirements(): array
    {
        return array_filter(
            $this->getRequirements(),
            function ($requirement) {
                return $requirement instanceof OroRequirement;
            }
        );
    }
}
