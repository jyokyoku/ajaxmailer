<?php

namespace Jyokyoku\AjaxMailer;

class Template
{
    protected $type;
    protected $vars = [];
    protected $leftBoundary = '';
    protected $rightBoundary = '';

    /**
     * Template constructor.
     * @param $type
     * @param array $vars
     * @param string $leftBoundary
     * @param string $rightBoundary
     */
    public function __construct(array $vars = [], $leftBoundary = '[', $rightBoundary = ']')
    {
        $this->vars = $vars;
        $this->leftBoundary = $leftBoundary;
        $this->rightBoundary = $rightBoundary;
    }

    /**
     * @return string
     */
    public function getDirPath()
    {
        return dirname(__DIR__) . '/templates/';
    }

    /**
     * @param $fileName
     * @return mixed
     */
    public function file($fileName)
    {
        $filePath = $this->getDirPath() . '/' . $fileName;

        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("File {$filePath} not exists.");
        }

        return $this->text(file_get_contents($filePath));
    }

    /**
     * @param $text
     * @return mixed
     */
    public function text($text)
    {
        foreach ($this->vars as $key => $value) {
            if (is_array($value)) {
                $implode = function (array $values, $separator = ',') use (&$implode) {
                    $returns = [];

                    foreach ($values as $value) {
                        if (is_array($value)) {
                            $returns[] = '[' . $implode($value) . ']';
                        } else {
                            $returns[] = $value;
                        }
                    }

                    return implode($separator, $returns);
                };

                $value = $implode($value, ', ');
            }

            $text = str_replace($this->leftBoundary . $key . $this->rightBoundary, $value, $text);
        }

        return $text;
    }
}