<?php

namespace OJezu\DynamicParameterBundle\Service;

class LocalJsonFileParameterProvider extends JsonParameterProvider
{
    /**
     * @var string
     */
    private $jsonFilePath;
    /**
     * @param string $jsonFilePath
     */
    public function __construct($jsonFilePath)
    {
        $this->jsonFilePath = $jsonFilePath;
    }

    /**
     * @return false|string
     */
    protected function getFileContents() {
        $contents = @file_get_contents($this->jsonFilePath);

        if ($contents === false) {
            // when only cache for $path is cleared by passing a second argument to clearstatcache
            // it still does not work half the time
            clearstatcache(true);
            $contents = file_get_contents($this->jsonFilePath);
        }

        return $contents;
    }
}
