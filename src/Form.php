<?php

namespace Jyokyoku\AjaxMailer;

class Form extends Config
{
    protected $message;
    protected $valid = false;
    protected $rules = [];
    protected $sanitizeFields = [];
    protected $data = [];
    protected $validated = [];
    protected $errors = [];

    /**
     * @return string
     */
    public function getConfigDirPath()
    {
        return dirname(__DIR__) . '/config/form/';
    }

    public function load()
    {
        parent::load();

        foreach ($this as $field => $config) {
            if (!empty($config['sanitize']) || !isset($config['sanitize'])) {
                $this->sanitizeFields[] = $field;
            }

            if (!empty($config['rules']) && is_array($config['rules'])) {
                foreach ($config['rules'] as $rule => $ruleArgs) {
                    if (preg_match('/^[0-9]+?$/', $rule)) {
                        $rule = $ruleArgs;
                        $ruleArgs = true;
                    }

                    if (!$ruleArgs) {
                        continue;
                    }

                    $rule = strtolower($rule);

                    if (is_callable([Validator::class, $rule])) {
                        $this->rules[$field][$rule] = array_values((array)$ruleArgs);
                    }
                }
            }
        }
    }

    /**
     * @param Message $message
     */
    public function setMessage(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;

        foreach ($this->data as $key => $value) {
            $value = trim($value);

            if (in_array($key, $this->sanitizeFields)) {
                $this->data[$key] = strip_tags($value);
            }
        }
    }

    /**
     * return bool
     */
    public function validate()
    {
        $this->valid = true;

        foreach ($this->rules as $field => $rules) {
            $value = isset($this->data[$field]) ? $this->data[$field] : null;

            foreach ($rules as $rule => $ruleArgs) {
                $label = isset($this[$field]['label']) ? $this[$field]['label'] : $field;
                $template = new Template($ruleArgs + ['label' => $label], '[', ']');

                array_unshift($ruleArgs, $value);
                $result = call_user_func_array([Validator::class, $rule], $ruleArgs);

                if ($result !== true) {
                    $this->valid = false;
                    $message = isset($this->message[$rule]) ? $template->text($this->message[$rule]) : $rule;
                    $this->errors[$field] = $message;

                    continue 2;
                }
            }

            $this->validated[$field] = $value;
        }

        return $this->valid;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return array
     */
    public function getValidated()
    {
        return $this->validated;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}