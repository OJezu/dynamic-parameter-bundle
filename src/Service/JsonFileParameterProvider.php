<?php

namespace OJezu\DynamicParameterBundle\Service;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class JsonFileParameterProvider implements ParameterProviderInterface
{
    /**
     * @var string
     */
    private $jsonFilePath;

    /**
     * @var null|array
     */
    private $fileContent;

    /**
     * @param string $jsonFilePath
     */
    public function __construct($jsonFilePath)
    {
        $this->jsonFilePath = $jsonFilePath;
    }

    /**
     * @param array $parameterPath
     *
     * @return string
     */
    public function get(array $parameterPath)
    {
        return $this->traversePath($this->jsonFileContent(), $parameterPath);
    }

    /**
     * @param array $parameterPath
     *
     * @return bool
     */
    public function has(array $parameterPath)
    {
        return (bool) $this->traversePath($this->jsonFileContent(), $parameterPath, true);
    }

    /**
     * @return array|mixed|null
     */
    private function jsonFileContent()
    {
        if (!$this->fileContent) {
            $this->fileContent = json_decode(file_get_contents($this->jsonFilePath), true);

            if (!$this->fileContent) {
                throw new InvalidConfigurationException(sprintf(
                    'Json file »%s« specified as parameter source is empty.',
                    $this->jsonFilePath
                ));
            }
        }

        return $this->fileContent;
    }

    /**
     * @param $array
     * @param $path
     * @param bool $checkMode
     *
     * @return bool|null
     */
    private function traversePath($array, $path, $checkMode = false)
    {
        $fullPath = $path;
        $exists = true;
        $result = null;

        while ($pathElement = array_shift($path)) {
            if (!array_key_exists($pathElement, $array)) {
                $exists = false;
                break;
            }

            $array = $array[$pathElement];
        }

        if (!$exists) {
            if ($checkMode) {
                $result = false;
            } else {
                throw new InvalidConfigurationException(sprintf(
                    'Element »%s« not found in JSON file',
                    implode('.', $fullPath)
                ));
            }
        } else {
            $result = $checkMode ? true : $array;
        }

        return $result;
    }
}
