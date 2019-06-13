<?php

namespace OJezu\DynamicParameterBundle\Service;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

abstract class JsonParameterProvider implements ParameterProviderInterface
{
    /**
     * @var null|array
     */
    private $fileContent;

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
     * @return false|string
     */
    abstract protected function getFileContents();

    /**
     * @return array|mixed|null
     */
    private function jsonFileContent()
    {
        if (!$this->fileContent) {
            $contents = $this->getFileContents();

            $this->fileContent = \GuzzleHttp\json_decode($contents, true);

            if (!$this->fileContent) {
                throw new InvalidConfigurationException(sprintf(
                    'Json specified as parameter source is empty.'
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
