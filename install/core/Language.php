<?php
/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

class Language{
    private $defaultLanguage = 'en_US';

    private $systemHelper;

    private $data = array();

    protected $defaultLabels = [
        'nginx' => 'linux',
        'apache' => 'linux',
        'microsoft-iis' => 'windows',
    ];

    public function __construct()
    {
        require_once 'SystemHelper.php';
        $this->systemHelper = new SystemHelper();
    }

    protected function getSystemHelper()
    {
        return $this->systemHelper;
    }

    public function get($language)
    {
        if (isset($this->data[$language])) {
            return $this->data[$language];
        }

        if (empty($language)) {
            $language = $this->defaultLanguage;
        }

        $langFileName = 'install/core/i18n/'.$language.'/install.json';
        if (!file_exists($langFileName)) {
            $langFileName = 'install/core/i18n/'.$this->defaultLanguage.'/install.json';
        }

        $i18n = $this->getLangData($langFileName);

        if ($language != $this->defaultLanguage) {
            $i18n = $this->mergeWithDefaults($i18n);
        }

        $this->afterRetrieve($i18n);

        $this->data[$language] = $i18n;

        return $this->data[$language];
    }

    /**
     * Merge current language with default one
     *
     * @param  array $data
     * @return array
     */
    protected function mergeWithDefaults($data)
    {
        $defaultLangFile = 'install/core/i18n/'.$this->defaultLanguage.'/install.json';
        $defaultData = $this->getLangData($defaultLangFile);

        foreach ($data as $categoryName => &$labels) {
            foreach ($defaultData[$categoryName] as $defaultLabelName => $defaultLabel) {
                if (!isset($labels[$defaultLabelName])) {
                    $labels[$defaultLabelName] = $defaultLabel;
                }
            }
        }

        $data = array_merge($defaultData, $data);

        return $data;
    }

    protected function getLangData($filePath)
    {
        $data = file_get_contents($filePath);
        $data = json_decode($data, true);

        return $data;
    }

    /**
     * After retrieve actions
     *
     * @param  array $i18n
     * @return array $i18n
     */
    protected function afterRetrieve(array &$i18n)
    {
        /** Get rewrite rules */
        $serverType = $this->getSystemHelper()->getServerType();
        $serverOs = $this->getSystemHelper()->getOs();

        $rewriteRules = $this->getSystemHelper()->getRewriteRules();

        $label = $i18n['options']['modRewriteInstruction'][$serverType][$serverOs] ?? null;

        if (!isset($label) && isset($this->defaultLabels[$serverType])) {
            $defaultLabel = $this->defaultLabels[$serverType];

            if (!isset($i18n['options']['modRewriteInstruction'][$serverType][$defaultLabel])) {
                $defaultLangFile = 'install/core/i18n/' . $this->defaultLanguage . '/install.json';
                $defaultData = $this->getLangData($defaultLangFile);

                $i18n['options']['modRewriteInstruction'][$serverType][$defaultLabel] = $defaultData['options']['modRewriteInstruction'][$serverType][$defaultLabel];
            }

            $label = $i18n['options']['modRewriteInstruction'][$serverType][$defaultLabel];
        }

        if (!$label) {
            return;
        }

        preg_match_all('/\{(.*?)\}/', $label, $match);
        if (isset($match[1])) {
            foreach ($match[1] as $varName) {
                if (isset($rewriteRules[$varName])) {
                    $label = str_replace('{'.$varName.'}', $rewriteRules[$varName], $label);
                }
            }
        }

        $i18n['options']['modRewriteInstruction'][$serverType][$serverOs] = $label;
    }
}
