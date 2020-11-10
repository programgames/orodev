<?php

namespace Programgames\OroDev\Requirements;

use InvalidArgumentException;
use Programgames\OroDev\Requirements\Tools\ArrayUtil;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlFileLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource,string $type = null)
    {
        $path = $this->locator->locate($resource);

        $content = Yaml::parse(file_get_contents($path));

        // empty file
        if (null === $content) {
            return array();
        }
        if (empty($content['parameters'])) {
            $content['parameters'] = array();
        }

        // imports
        $importedParameters = $this->parseImports($content, $path);
        $content['parameters'] = ArrayUtil::arrayMergeRecursiveDistinct($content['parameters'], $importedParameters);

        // parameters
        if (isset($content['parameters'])) {
            return $content['parameters'];
        }

        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource,string $type = null)
    {
        return is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), array('yml', 'yaml'), true);
    }

    /**
     * Parses all imports.
     *
     * @param array $content
     * @param string $file
     * @return array
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     */
    private function parseImports($content, $file)
    {
        if (!isset($content['imports'])) {
            return array();
        }

        if (!is_array($content['imports'])) {
            throw new InvalidArgumentException(sprintf('The "imports" key should contain an array in %s. Check your YAML syntax.', $file));
        }

        $defaultDirectory = dirname($file);
        $importedParameters = array();
        foreach ($content['imports'] as $import) {
            if (!is_array($import)) {
                throw new InvalidArgumentException(sprintf('The values in the "imports" key should be arrays in %s. Check your YAML syntax.', $file));
            }

            $this->setCurrentDir($defaultDirectory);
            $importedContent = (array)$this->import($import['resource'], null, isset($import['ignore_errors']) ? (bool) $import['ignore_errors'] : false, $file);
            if (is_array($importedContent)) {
                $importedParameters = ArrayUtil::arrayMergeRecursiveDistinct($importedParameters, $importedContent);
            }
        }

        return $importedParameters;
    }
}
