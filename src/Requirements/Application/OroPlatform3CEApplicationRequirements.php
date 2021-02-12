<?php

namespace Programgames\OroDev\Requirements\Application;

use Exception;
use Programgames\OroDev\Tools\VersionChecker\NodeJsVersionChecker;

/**
 * This class specifies all requirements and optional recommendations that are necessary to run the Oro Application.
 */
class OroPlatform3CEApplicationRequirements extends OroRequirements implements OroApplicationRequirementsInterface
{
    const REQUIRED_PHP_VERSION = '7.1.26';
    const REQUIRED_GD_VERSION = '2.0';
    const REQUIRED_CURL_VERSION = '7.0';
    const REQUIRED_NODEJS_VERSION = '>=6.6';

    const EXCLUDE_REQUIREMENTS_MASK = '/5\.[0-6]|7\.0/';

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
}
