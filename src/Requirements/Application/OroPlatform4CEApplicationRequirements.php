<?php

namespace Programgames\OroDev\Requirements\Application;

use Exception;
use Programgames\OroDev\Requirements\OroRequirement;
use Programgames\OroDev\Tools\VersionChecker\NodeJsVersionChecker;
use Symfony\Requirements\PhpConfigRequirement;

/**
 * This class specifies all requirements and optional recommendations that are necessary to run the Oro Application.
 */
class OroPlatform4CEApplicationRequirements extends OroRequirements implements OroApplicationRequirementsInterface
{
    public const REQUIRED_PHP_VERSION = '7.3.13';
    public const REQUIRED_GD_VERSION = '2.0';
    public const REQUIRED_CURL_VERSION = '7.0';
    public const REQUIRED_NODEJS_VERSION = '>=12.0';

    /**
     * @param NodeJsVersionChecker $nodeJsVersionChecker
     * @param string $env
     * @throws Exception
     */
    public function __construct(NodeJsVersionChecker $nodeJsVersionChecker, $env = 'prod')
    {
        parent::__construct(
            $nodeJsVersionChecker,
            self::REQUIRED_PHP_VERSION,
            self::REQUIRED_GD_VERSION,
            self::REQUIRED_CURL_VERSION,
            self::REQUIRED_NODEJS_VERSION,
            $env
        );
    }

    /** @noinspection SenselessMethodDuplicationInspection */
    public function getMandatoryRequirements(): array
    {
        return array_filter(
            $this->getRequirements(),
            static function ($requirement) {
                return !($requirement instanceof PhpConfigRequirement)
                    && !($requirement instanceof OroRequirement);
            }
        );
    }

    /** @noinspection SenselessMethodDuplicationInspection */
    public function getPhpConfigRequirements(): array
    {
        return array_filter(
            $this->getRequirements(),
            static function ($requirement) {
                return $requirement instanceof PhpConfigRequirement;
            }
        );
    }

    /** @noinspection SenselessMethodDuplicationInspection */
    public function getOroRequirements(): array
    {
        return array_filter(
            $this->getRequirements(),
            static function ($requirement) {
                return $requirement instanceof OroRequirement;
            }
        );
    }
}
