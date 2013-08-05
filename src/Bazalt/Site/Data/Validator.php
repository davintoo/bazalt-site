<?php

namespace Bazalt\Site\Data;

class Validator extends \Bazalt\Data\Validator
{
    /**
     * @param array $data
     * @return Validator
     */
    public static function create($data = [])
    {
        return new Validator($data);
    }

    /**
     * @param $name
     * @return ValidationSet
     */
    public function localizableField($name)
    {
        $site = \Bazalt\Site::get();
        $languages = $site->getLanguages();

        $defaultLanguageValidator = Validator::create()
            ->field($site->language_id)
            ->required();

        $keys = array_keys($languages);
        $keys []= 'orig'; // original flag

        $field = new ValidationSet($this);
        $field->keys($keys) // allow only exists languages keys
              ->nested(                           // required for default language
                    $defaultLanguageValidator->end()
              );

        $this->fields[$name] = $field;
        return $defaultLanguageValidator;
    }
}