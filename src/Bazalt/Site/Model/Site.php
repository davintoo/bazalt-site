<?php

namespace Bazalt\Site\Model;
use Bazalt\ORM;

/**
 * @property Site|null originalSite Оригінальний сайт, з якого був здійснений редірект
 */
class Site extends Base\Site
{
    private $_languages = null;

    public static function create()
    {
        $site = new Site();
        $site->language_id = 'en';
        $site->languages = 'en';
        $site->is_allow_indexing = 0;
        return $site;
    }

    public static function getSiteByDomain($domain, $onlyActive = true)
    {
         $q = ORM::select('Bazalt\\Site\\Model\\Site s')
                 ->where('s.domain = ?', $domain);

         if ($onlyActive) {
             $q->andWhere('s.is_active = ?', 1);
         }
         return $q->fetch();
    }

    public function getUrl()
    {
        return 'http://' . $this->domain . $this->path;
    }

    /**
     * Добавляет язык на сайт
     *
     * @param Language $language
     */
    public function addLanguage(Language $language)
    {
        $this->Languages->add($language, ['is_active' => 1]);

        $langs = explode(',', $this->languages);
        $langs []= $language->id;
        $langs = array_unique($langs);
        $this->languages = implode(',', $langs);
        $this->save();
        if ($this->_languages) {
            $this->_languages[$language->id] = $language;
        }
    }

    /**
     * Удаляет язык с сайта
     *
     * @param Language $language
     */
    public function removeLanguage(Language $language)
    {
        $this->Languages->remove($language);

        $langs = explode(',', $this->languages);
        $langs = array_diff($langs, [$language->id]);
        $this->languages = implode(',', $langs);
        $this->save();
        if ($this->_languages) {
            unset($this->_languages[$language->id]);
        }
    }

    /**
     * Возвращает все языки, которые доступны на сайте
     *
     * @return Language[]
     */
    public function getLanguages()
    {
        if (!$this->_languages) {
            $langs = explode(',', $this->languages);
            $q = Language::select()->whereIn('id', $langs);
            $languages = $q->fetchAll();
            $this->_languages = [];

            // fill array in order of $this->languages
            foreach ($langs as $l) {
                foreach ($languages as $lang) {
                    if ($lang->id == $l) {
                        $this->_languages[$lang->id] = $lang;
                    }
                }
            }
        }
        return $this->_languages;
    }

    /**
     * Проверяет наличие языка на сайте
     *
     * @param $alias
     * @return bool
     */
    public function hasLanguage($alias)
    {
        return in_array($alias, explode(',', $this->languages));
    }

    public static function getCollection()
    {
        $q = Site::select();

        return new \Bazalt\ORM\Collection($q);
    }

    public function toArray()
    {
        $res = parent::toArray();

        $res['is_subdomain'] = $this->is_subdomain == '1';
        $res['is_active'] = $this->is_active == '1';
        return $res;
    }
}