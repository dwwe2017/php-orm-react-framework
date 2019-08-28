<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

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
    private $modTranslator;

    /**
     * @var Translator
     */
    private $sysTranslator;

    /**
     * @var string
     */
    private $sysLocaleDir = "";

    /**
     * @var string
     */
    private $modLocaleDir = "";

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
        $language = $config->get("language");

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
        $this->modTranslator->setLanguage($language);
        $this->modTranslator->loadDomain(self::DOMAIN, $this->modLocaleDir);
        $this->modTranslator->register();

        /**
         * @see LocaleService::getSystemTranslator()
         * System translation
         */
        $this->sysTranslator = new Translator();
        $this->sysTranslator->loadTranslations($this->getTranslations($language));
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
     * @param string $localeCode
     * @return Translations
     */
    private function getTranslations(string $localeCode)
    {
        return $this->getSystemTranslations($localeCode)->mergeWith($this->getModuleTranslations($localeCode));
    }
}