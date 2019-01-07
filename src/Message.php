<?php

namespace Jyokyoku\AjaxMailer;

class Message extends Config
{
    public $locale = '';

    /**
     * @param $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getConfigDirPath()
    {
        return dirname(__DIR__) . '/config/message/';
    }

    /**
     * @param $configType
     * @return string
     */
    public function getConfigFilePath()
    {
        if ($this->locale) {
            $filePath = $this->getConfigDirPath() . $this->type . '.' . $this->locale . '.yml';

        } else {
            $filePath = $this->getConfigDirPath() . $this->type . '.yml';
        }

        if (!is_readable($filePath)) {
            if ($this->locale) {
                $filePath = $this->getConfigDirPath() . 'default.' . $this->locale . '.yml';

            } else {
                $filePath = $this->getConfigDirPath() . 'default.yml';
            }
        }

        return $filePath;
    }
}
