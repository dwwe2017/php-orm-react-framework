<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Services;


use Exceptions\LocaleException;
use Gettext\GettextTranslator;
use Gettext\Translations;
use Gettext\Translator;
use Helpers\DeclarationHelper;
use Helpers\FileHelper;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class LocaleService
 * @package Services
 */
class LocaleService implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

    /**
     * @var string
     */
    const DOMAIN = "messages";

    /**
     * @var GettextTranslator
     */
    private GettextTranslator $modTranslator;

    /**
     * @var Translator
     */
    private Translator $sysTranslator;

    /**
     * @var string
     */
    private $sysLocaleDir = "";

    /**
     * @var string
     */
    private $modLocaleDir = "";

    /**
     * @var string
     */
    private $languageCode = "";

    /**
     * LocaleService constructor.
     * @see ServiceManager::__construct()
     * @param ModuleManager $moduleManager
     */
    public final function __construct(ModuleManager $moduleManager)
    {
        DeclarationHelper::init(null, null, "gettext",
            LocaleException::class)->functionExists();

        $config = $moduleManager->getConfig();
        $baseDir = $config->get("base_dir");
        $this->languageCode = $config->get("language");

        $this->sysLocaleDir = sprintf("%s/locale", $baseDir);
        $this->modLocaleDir = sprintf("%s/locale", $moduleManager->getModuleBaseDir());
        if(!FileHelper::init($this->modLocaleDir)->isReadable()){
            $this->modLocaleDir = $this->sysLocaleDir;
        }

        /**
         * @see LocaleService::getModuleTranslator()
         * Module translation
         */
        $this->modTranslator = new GettextTranslator();
        $this->modTranslator->setLanguage($this->getLanguageCode());
        $this->modTranslator->loadDomain(self::DOMAIN, $this->modLocaleDir);
        $this->modTranslator->register();

        /**
         * @see LocaleService::getSystemTranslator()
         * System translation
         */
        $this->sysTranslator = new Translator();
        $this->sysTranslator->loadTranslations($this->getTranslations($this->getLanguageCode()));
        $this->sysTranslator->register();
    }

    /**
     * Contains the global functions for the Twig extension il8n for translation in template files.
     * For translation in twig files with {% trans %} text {% endtrans %}.
     * Important: Here only language files of the current module are accessed!
     * @return GettextTranslator
     */
    public final function getModuleTranslator(): GettextTranslator
    {
        return $this->modTranslator;
    }

    /**
     * Contains the global function __() for translations.
     * Important: Here files of the current module and the system are accessed!
     * @return Translator
     */
    public final function getSystemTranslator(): Translator
    {
        return $this->sysTranslator;
    }

    /**
     * @param string $localeCode
     */
    public final function setLanguage(string $localeCode): void
    {
        $this->modTranslator->setLanguage($localeCode);
        $this->sysTranslator->loadTranslations($this->getTranslations($localeCode));
    }

    /**
     * @param string $localeCode
     * @return Translations
     */
    private function getSystemTranslations(string $localeCode)
    {
        $poFile = sprintf("%s/%s/LC_%s/%s.po", $this->sysLocaleDir,
            $localeCode, strtoupper(self::DOMAIN), self::DOMAIN);

        if(!FileHelper::init($poFile)->isReadable()){
            return new Translations([]);
        }

        return Translations::fromPoFile($poFile);
    }

    /**
     * @param string $localeCode
     * @return Translations
     */
    private function getModuleTranslations(string $localeCode)
    {
        $poFile = sprintf("%s/%s/LC_%s/%s.po", $this->modLocaleDir,
            $localeCode, strtoupper(self::DOMAIN), self::DOMAIN);

        if(!FileHelper::init($poFile)->isReadable()){
            return new Translations([]);
        }

        return Translations::fromPoFile($poFile);
    }

    /**
     * @param string|null $localeCode
     * @return Translations
     */
    public function getTranslations(?string $localeCode = null)
    {
        $localeCode = is_null($localeCode) ? $this->getLanguageCode() : $localeCode;
        return $this->getSystemTranslations($localeCode)->mergeWith($this->getModuleTranslations($localeCode));
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }
}
