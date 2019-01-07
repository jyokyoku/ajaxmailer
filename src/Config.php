<?php

namespace Jyokyoku\AjaxMailer;

use Symfony\Component\Yaml\Yaml;

class Config implements \ArrayAccess, \IteratorAggregate
{
    protected $type = '';
    protected $registry = [];

    protected static $instances = [];

    /**
     * Config constructor.
     * @param $configType
     */
    protected function __construct($configType)
    {
        $this->type = $configType;
    }

    public function load()
    {
        $configFile = $this->getConfigFilePath();

        if (is_readable($configFile)) {
            $this->registry = Yaml::parse(file_get_contents($configFile));
        }
    }

    /**
     * @param $configType
     * @return string
     */
    public function getConfigFilePath()
    {
        $filePath = $this->getConfigDirPath() . $this->type . '.yml';

        if (!is_readable($filePath)) {
            $filePath = $this->getConfigDirPath() . 'default.yml';
        }

        return $filePath;
    }

    /**
     * @return string
     */
    public function getConfigDirPath()
    {
        return dirname(__DIR__) . '/config/';
    }

    /**
     * @param string|array $key
     * @param null $default
     * @return array|mixed|null
     */
    public function read($key, $default = null)
    {
        if (is_array($key)) {
            $return = [];

            foreach ($key as $subKey => $subDefault) {
                if (is_int($subKey) && (is_string($subDefault) || is_numeric($subDefault))) {
                    $subKey = (string)$subDefault;
                    $subDefault = $default;
                }

                $return[$subKey] = static::read($subKey, $subDefault);
            }

            return $return;
        }

        $keyChunks = explode('.', $key);
        $return = $this->registry;

        foreach ($keyChunks as $i => $keyChunk) {
            if (!is_array($return) || (!array_key_exists($keyChunk, $return))) {
                return $default;
            }

            $return = $return[$keyChunk];
        }

        return $return;
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->registry);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->registry[$offset]) ? $this->registry[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->registry[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->registry[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->registry[$offset]);
    }

    /**
     * @param $configType
     * @return static
     */
    public static function getInstance($configType)
    {
        if (empty(static::$instances[static::class][$configType])) {
            static::$instances[static::class][$configType] = new static($configType);
        }

        return static::$instances[static::class][$configType];
    }
}