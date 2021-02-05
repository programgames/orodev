<?php

namespace Programgames\OroDev\Requirements\Application;

use Exception;
use PDO;
use Programgames\OroDev\Requirements\DbPrivilegesProvider;
use Programgames\OroDev\Requirements\OroRequirement;
use Programgames\OroDev\Requirements\Tools\NodeJsExecutableFinder;
use Programgames\OroDev\Requirements\Tools\NodeJsVersionChecker;
use Programgames\OroDev\Requirements\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Process\Process;
use Symfony\Requirements\PhpConfigRequirement;
use Symfony\Requirements\SymfonyRequirements;

class OroRequirements extends SymfonyRequirements
{
    const EXCLUDE_REQUIREMENTS_MASK = '/5\.[0-6]|7\.0/';

    /**
     * OroRequirements constructor.
     * @param string $requiredPhpVersion
     * @param string $requiredGdVersion
     * @param string $requiredCurlVersion
     * @param string $requiredNodeJsVersion
     * @param string $env
     * @throws Exception
     */
    public function __construct(
        string $requiredPhpVersion,
        string $requiredGdVersion,
        string $requiredCurlVersion,
        string $requiredNodeJsVersion,
        $env = 'prod'
    ) {
        parent::__construct();

        $this->addOroRequirements(
            $requiredPhpVersion,
            $requiredGdVersion,
            $requiredCurlVersion,
            $requiredNodeJsVersion,
            $env
        );
    }

    /**
     * @param string $requiredPhpVersion
     * @param string $requiredGdVersion
     * @param string $requiredCurlVersion
     * @param string $requiredNodeJsVersion
     * @param string $env
     * @throws Exception
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function addOroRequirements(
        string $requiredPhpVersion,
        string $requiredGdVersion,
        string $requiredCurlVersion,
        string $requiredNodeJsVersion,
        $env = 'prod'
    ) {
        $phpVersion = phpversion();
        $oldLevel = null;

        /**
         * We should hide the deprecation warnings for php >= 7.2 because SymfonyRequirements class uses
         * 'create_function' function that was deprecated in php 7.2.
         *
         * @see http://php.net/manual/en/migration72.deprecated.php#migration72.deprecated.create_function-function
         * @see https://github.com/sensiolabs/SensioDistributionBundle/pull/336
         */
        if (version_compare($phpVersion, '7.2', '>=')) {
            $oldLevel = error_reporting(E_ALL & ~E_DEPRECATED);
        }

        parent::__construct();

        // restore the previous report level in casse of php > 7.2.
        if (version_compare($phpVersion, '7.2', '>=')) {
            error_reporting($oldLevel);
        }

        $gdVersion = defined('GD_VERSION') ? GD_VERSION : null;
        $curlVersion = function_exists('curl_version') ? curl_version() : null;
        $icuVersion = Intl::getIcuVersion();

        $this->addOroRequirement(
            version_compare($phpVersion, $requiredPhpVersion, '>='),
            sprintf('PHP version must be at least %s (%s installed)', $requiredPhpVersion, $phpVersion),
            sprintf(
                'You are running PHP version "<strong>%s</strong>", but Oro needs at least PHP "<strong>%s</strong>" to run.' .
                'Before using Oro, upgrade your PHP installation, preferably to the latest version.',
                $phpVersion,
                $requiredPhpVersion
            ),
            sprintf('Install PHP %s or newer (installed version is %s)', $requiredPhpVersion, $phpVersion)
        );

        $this->addOroRequirement(
            null !== $gdVersion && version_compare($gdVersion, $requiredGdVersion, '>='),
            'GD extension must be at least ' . $requiredGdVersion,
            'Install and enable the <strong>GD</strong> extension at least ' . $requiredGdVersion . ' version'
        );

        $this->addOroRequirement(
            null !== $curlVersion && version_compare($curlVersion['version'], $requiredCurlVersion, '>='),
            'cURL extension must be at least ' . $requiredCurlVersion,
            'Install and enable the <strong>cURL</strong> extension at least ' . $requiredCurlVersion . ' version'
        );

        $this->addOroRequirement(
            function_exists('openssl_encrypt'),
            'openssl_encrypt() should be available',
            'Install and enable the <strong>openssl</strong> extension.'
        );

        if (function_exists('iconv')) {
            $this->addOroRequirement(
                false !== @iconv('utf-8', 'ascii//TRANSLIT', 'check string'),
                'iconv() must not return the false result on converting string "check string"',
                'Check the configuration of the <strong>iconv</strong> extension, '
                . 'as it may have been configured incorrectly'
                . ' (iconv(\'utf-8\', \'ascii//TRANSLIT\', \'check string\') must not return false).'
            );
        }

        $this->addOroRequirement(
            class_exists('Locale'),
            'intl extension should be available',
            'Install and enable the <strong>intl</strong> extension.'
        );

        $localeCurrencies = [
            'de_DE' => 'EUR',
            'en_CA' => 'CAD',
            'en_GB' => 'GBP',
            'en_US' => 'USD',
            'fr_FR' => 'EUR',
            'uk_UA' => 'UAH',
        ];

        foreach ($localeCurrencies as $locale => $currencyCode) {
            /** @noinspection PhpUndefinedClassInspection */
            $numberFormatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

            /** @noinspection PhpUndefinedClassInspection */
            if ($currencyCode === $numberFormatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE)) {
                unset($localeCurrencies[$locale]);
            }
        }

        $this->addRecommendation(
            empty($localeCurrencies),
            sprintf('Current version %s of the ICU library should meet the requirements', $icuVersion),
            sprintf(
                'There may be a problem with currency formatting in <strong>ICU</strong> %s, ' .
                'please upgrade your <strong>ICU</strong> library.',
                $icuVersion
            )
        );

        $this->addOroRequirement(
            class_exists('ZipArchive'),
            'zip extension should be installed',
            'Install and enable the <strong>Zip</strong> extension.'
        );

        $this->addRecommendation(
            class_exists('SoapClient'),
            'SOAP extension should be installed (API calls)',
            'Install and enable the <strong>SOAP</strong> extension.'
        );

        $this->addRecommendation(
            extension_loaded('tidy'),
            'Tidy extension should be installed to make sure that any HTML is correctly converted into a text representation.',
            'Install and enable the <strong>Tidy</strong> extension.'
        );

        $this->addRecommendation(
            !extension_loaded('phar'),
            'Phar extension is disabled',
            'Disable <strong>Phar</strong> extension to reduce the risk of PHP unserialization vulnerability.'
        );

        $this->addRecommendation(
            extension_loaded('imap'),
            'IMAP extension should be installed for valid email processing on IMAP sync.',
            'Install and enable the <strong>IMAP</strong> extension.'
        );

        $tmpDir = sys_get_temp_dir();
        $this->addRequirement(
            is_writable($tmpDir),
            sprintf('%s (sys_get_temp_dir()) directory must be writable', $tmpDir),
            sprintf(
                'Change the permissions of the "<strong>%s</strong>" directory ' .
                'or the result of <string>sys_get_temp_dir()</string> ' .
                'or add the path to php <strong>open_basedir</strong> list. ' .
                'So that it would be writable.',
                $tmpDir
            )
        );

        // Windows specific checks
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->addRecommendation(
                function_exists('finfo_open'),
                'finfo_open() should be available',
                'Install and enable the <strong>Fileinfo</strong> extension.'
            );

            $this->addRecommendation(
                class_exists('COM'),
                'COM extension should be installed',
                'Install and enable the <strong>COM</strong> extension.'
            );
        }

        // Unix specific checks
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->addRequirement(
                $this->checkFileNameLength(),
                'Maximum supported filename length must be greater or equal 242 characters.' .
                ' Make sure that the cache folder is not inside the encrypted directory.',
                'Move <strong>var/cache</strong> folder outside encrypted directory.',
                'Maximum supported filename length must be greater or equal 242 characters.' .
                ' Move var/cache folder outside encrypted directory.'
            );
        }

        $baseDir = getcwd();
        $mem = $this->getBytes(ini_get('memory_limit'));

        $this->addPhpConfigRequirement(
            'memory_limit',
            function () use ($mem) {
                return $mem >= 512 * 1024 * 1024 || -1 == $mem;
            },
            false,
            'memory_limit should be at least 512M',
            'Set the "<strong>memory_limit</strong>" setting in php.ini to at least "512M".'
        );
        $nodeJsExecutableFinder = new NodeJsExecutableFinder();
        $nodeJsExecutable = $nodeJsExecutableFinder->findExecutable();
        $nodeJsExists = null !== $nodeJsExecutable;
        $this->addOroRequirement(
            $nodeJsExists,
            $nodeJsExists ? 'NodeJS is installed' : 'NodeJS must be installed',
            'Install <strong>NodeJS</strong>.'
        );

        $this->addOroRequirement(
            NodeJsVersionChecker::satisfies($nodeJsExecutable, $requiredNodeJsVersion),
            sprintf('NodeJS "%s" version must be installed.', $requiredNodeJsVersion),
            sprintf('Upgrade <strong>NodeJS</strong> to "%s" version.', $requiredNodeJsVersion)
        );

        $npmExists = null !== $nodeJsExecutableFinder->findNpm();
        $this->addOroRequirement(
            $npmExists,
            $npmExists ? 'NPM is installed' : 'NPM must be installed',
            'Install <strong>NPM</strong>.'
        );

        $this->addOroRequirement(
            is_writable($baseDir . '/public/uploads'),
            'public/uploads/ directory must be writable',
            'Change the permissions of the "<strong>public/uploads/</strong>" directory so that the web server can write into it.'
        );
        $this->addOroRequirement(
            is_writable($baseDir . '/public/media'),
            'public/media/ directory must be writable',
            'Change the permissions of the "<strong>public/media/</strong>" directory so that the web server can write into it.'
        );
        $this->addOroRequirement(
            is_writable($baseDir . '/public/bundles'),
            'public/bundles/ directory must be writable',
            'Change the permissions of the "<strong>public/bundles/</strong>" directory so that the web server can write into it.'
        );
        $this->addOroRequirement(
            is_writable($baseDir . '/var/attachment'),
            'var/attachment/ directory must be writable',
            'Change the permissions of the "<strong>var/attachment/</strong>" directory so that the web server can write into it.'
        );
        $this->addOroRequirement(
            is_writable($baseDir . '/var/import_export'),
            'var/import_export/ directory must be writable',
            'Change the permissions of the "<strong>var/import_export/</strong>" directory so that the web server can write into it.'
        );

        if (is_dir($baseDir . '/public/js')) {
            $this->addOroRequirement(
                is_writable($baseDir . '/public/js'),
                'public/js directory must be writable',
                'Change the permissions of the "<strong>public/js</strong>" directory so that the web server can write into it.'
            );
        }

        if (is_dir($baseDir . '/public/css')) {
            $this->addOroRequirement(
                is_writable($baseDir . '/public/css'),
                'public/css directory must be writable',
                'Change the permissions of the "<strong>public/css</strong>" directory so that the web server can write into it.'
            );
        }

        if (!is_dir($baseDir . '/public/css') || !is_dir($baseDir . '/public/js')) {
            $this->addOroRequirement(
                is_writable($baseDir . '/public'),
                'public directory must be writable',
                'Change the permissions of the "<strong>public</strong>" directory so that the web server can write into it.'
            );
        }

        if (is_file($baseDir . '/config/parameters.yml')) {
            $this->addOroRequirement(
                is_writable($baseDir . '/config/parameters.yml'),
                'config/parameters.yml file must be writable',
                'Change the permissions of the "<strong>config/parameters.yml</strong>" file so that the web server can write into it.'
            );
        }

        // Check database configuration
        $configYmlPath = $baseDir . '/config/config_' . $env . '.yml';
        if (is_file($configYmlPath)) {
            $config = $this->getParameters($configYmlPath);
            $pdo = $this->getDatabaseConnection($config);
            if ($pdo) {
                $requiredPrivileges = [
                    'INSERT',
                    'SELECT',
                    'UPDATE',
                    'DELETE',
                    'TRUNCATE',
                    'REFERENCES',
                    'TRIGGER',
                    'CREATE',
                    'DROP'
                ];
                $notGrantedPrivileges = $this->getNotGrantedPrivileges($pdo, $requiredPrivileges, $config);
                $this->addOroRequirement(
                    empty($notGrantedPrivileges),
                    sprintf('%s database privileges must be granted', implode(', ', $requiredPrivileges)),
                    sprintf(
                        'Grant %s privileges on database "%s" to user "%s"',
                        implode(', ', $notGrantedPrivileges),
                        $config['database_name'],
                        $config['database_user']
                    )
                );
                $this->addOroRequirement(
                    $this->isUuidSqlFunctionPresent($pdo),
                    'UUID SQL function must be present',
                    'Execute "<strong>CREATE EXTENSION IF NOT EXISTS "uuid-ossp";</strong>" SQL command so UUID-OSSP extension will be installed for database.'
                );
            }
        }
    }

    /**
     * Adds an Oro specific requirement.
     *
     * @param Boolean $fulfilled Whether the requirement is fulfilled
     * @param string $testMessage The message for testing the requirement
     * @param string $helpHtml The help text formatted in HTML for resolving the problem
     * @param string|null $helpText The help text (when null, it will be inferred from $helpHtml, i.e. stripped from HTML tags)
     */
    public function addOroRequirement(bool $fulfilled, string $testMessage, string $helpHtml, $helpText = null)
    {
        $this->add(new OroRequirement($fulfilled, $testMessage, $helpHtml, $helpText, false));
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

    /**
     * @param string $val
     * @return int
     */
    protected function getBytes(string $val)
    {
        if (empty($val)) {
            return 0;
        }

        preg_match('/([\-0-9]+)[\s]*([a-z]*)$/i', trim($val), $matches);

        if (isset($matches[1])) {
            $val = (int)$matches[1];
        }

        switch (strtolower($matches[2])) {
            case 'g':
            case 'gb':
                $val *= 1024*1024*1024;
                break;
            case 'm':
                break;
            case 'mb':
                $val *= 1024*1024;
                break;
            case 'k':
            case 'kb':
                $val *= 1024;
        }

        return (float)$val;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequirements(): array
    {
        $requirements = parent::getRequirements();

        foreach ($requirements as $key => $requirement) {
            if (!$requirement instanceof OroRequirement) {
                $testMessage = $requirement->getTestMessage();
                if (preg_match_all(self::EXCLUDE_REQUIREMENTS_MASK, $testMessage, $matches)) {
                    unset($requirements[$key]);
                }
            }
        }

        return $requirements;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecommendations(): array
    {
        $recommendations = parent::getRecommendations();

        foreach ($recommendations as $key => $recommendation) {
            $testMessage = $recommendation->getTestMessage();
            if (preg_match_all(self::EXCLUDE_REQUIREMENTS_MASK, $testMessage, $matches)) {
                unset($recommendations[$key]);
            }
        }

        return $recommendations;
    }

    /**
     * @return bool
     */
    protected function checkFileNameLength(): bool
    {
        $getConf = new Process(['getconf', 'NAME_MAX', __DIR__]);

        if (isset($_SERVER['PATH'])) {
            $getConf->setEnv(array('PATH' => $_SERVER['PATH']));
        }
        $getConf->run();

        if ($getConf->getErrorOutput()) {
            // getconf not installed
            return true;
        }

        $fileLength = trim($getConf->getOutput());

        return $fileLength >= 242;
    }

    /**
     * @param PDO $pdo
     * @return bool
     */
    protected function isUuidSqlFunctionPresent(PDO $pdo): bool
    {
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            try {
                $version = $pdo->query(
                    "-- noinspection SqlNoDataSourceInspection

SELECT extversion FROM pg_extension WHERE extname = 'uuid-ossp'"
                )->fetchColumn(
                );

                return !empty($version);
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param PDO $pdo
     * @param array $requiredPrivileges
     * @param array $config
     * @return array
     */
    protected function getNotGrantedPrivileges(PDO $pdo, array $requiredPrivileges, array $config): array
    {
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $granted = DbPrivilegesProvider::getPostgresGrantedPrivileges($pdo, $config['database_name']);
        } else {
            $granted = DbPrivilegesProvider::getMySqlGrantedPrivileges($pdo, $config['database_name']);
            if (in_array('ALL PRIVILEGES', $granted, true)) {
                $granted = $requiredPrivileges;
            }
        }

        return array_diff($requiredPrivileges, $granted);
    }

    /**
     * @param array $config
     * @return bool
     */
    protected function isPdoDriver(array $config): bool
    {
        return !empty($config['database_driver']) && strpos($config['database_driver'], 'pdo') === 0;
    }

    /**
     * @param array $config
     * @return bool|null|PDO
     */
    protected function getDatabaseConnection(array $config)
    {
        if ($config && $this->isPdoDriver($config)) {
            $driver = str_replace('pdo_', '', $config['database_driver']);
            $dsnParts = array(
                'host=' . $config['database_host'],
            );
            if (!empty($config['database_port'])) {
                $dsnParts[] = 'port=' . $config['database_port'];
            }
            $dsnParts[] = 'dbname=' . $config['database_name'];

            try {
                return new PDO(
                    $driver . ':' . implode(';', $dsnParts),
                    $config['database_user'],
                    $config['database_password']
                );
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * @param string $parametersYmlPath
     * @return array
     * @throws Exception
     */
    protected function getParameters(string $parametersYmlPath): array
    {
        $fileLocator = new FileLocator();
        $loader = new YamlFileLoader($fileLocator);

        return $loader->load($parametersYmlPath);
    }
}
