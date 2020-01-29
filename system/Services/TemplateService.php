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


use Configs\PortalConfig;
use Interfaces\ServiceInterfaces\VendorExtensionServiceInterface;
use Managers\ModuleManager;
use Traits\ServiceTraits\VendorExtensionInitServiceTraits;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Twig_Extensions_Extension_I18n;

/**
 * Class TemplateService
 * @package Services
 */
class TemplateService implements VendorExtensionServiceInterface
{
    use InstantiationStaticsUtilTrait;
    use VendorExtensionInitServiceTraits;

    /**
     * @var FilesystemLoader
     */
    private FilesystemLoader $loader;

    /**
     * @var Environment
     */
    private Environment $environment;

    /**
     * @var string
     */
    private $assetBaseDir = "";

    /**
     * TemplateService constructor.
     * @see ServiceManager::__construct()
     * @param ModuleManager $moduleManager
     */
    public final function __construct(ModuleManager $moduleManager)
    {
        $config = $moduleManager->getConfig();
        $baseDir = $config->get("base_dir");

        $this->assetBaseDir = !$moduleManager->isModule() ? "assets/"
            : str_replace(
                sprintf("%s/", $baseDir), "",
                sprintf("%s/assets/", $moduleManager->getModuleBaseDir())
            );

        $moduleTplDir = !$moduleManager->isModule() ? null
            : sprintf("modules/%s/views", $moduleManager->getModuleShortName());

        $mainTplDir = sprintf("templates/Controllers/%s", $config->get("template_options.template"));

        $filesystemLoaderPaths = is_null($moduleTplDir) ? ["views", $mainTplDir]
            : [$moduleTplDir, "views", $mainTplDir];

        $this->loader = new FilesystemLoader($filesystemLoaderPaths, $baseDir);

        $envOptions = $config->get("template_options", []);
        $this->environment = new Environment($this->loader, $envOptions);
        $this->environment->addExtension(new Twig_Extensions_Extension_I18n());

        $portalOptions = $config->get("portal_options", []);

        /**
         * A global variable is like any other template variable, except that it's available in all templates and macros:
         * @see https://twig.symfony.com/doc/3.x/advanced.html#globals
         * @see PortalConfig
         */
        if(!empty($portalOptions)){
            foreach ($portalOptions as $key => $portalOption){
                $this->environment->addGlobal($key, $portalOption);
            }
        }
    }

    /**
     * @return Environment
     */
    public final function getEnvironment(): Environment
    {
        /**
         * @see sha1()
         */
        $this->environment->addFunction(new TwigFunction("sha1", function (string $string){
            return sha1($string);
        }));

        /**
         * @see md5()
         */
        $this->environment->addFunction(new TwigFunction("md5", function (string $string){
            return md5($string);
        }));

        /**
         * @internal For developement
         * @see inc/helper.inc.php::print_pre()
         */
        $this->environment->addFunction(new TwigFunction("print_pre", function ($mixed){
            return print_pre($mixed);
        }));

        /**
         * @internal For output of user input that should support HTML code
         * @see inc/helper.inc.php::purify()
         */
        $this->environment->addFunction(new TwigFunction("purify", function (string $string){
            return purify($string);
        }));

        /**
         * @internal Default view helper function for user input to be reissued or saved
         * @see inc/helper.inc.php::clean()
         */
        $this->environment->addFunction(new TwigFunction("clean", function (string $string){
            return clean($string);
        }));

        /**
         * @internal Translation (singular)
         * @see LocaleService::init()
         */
        $this->environment->addFunction(new TwigFunction("__", function (string $original){
            return __($original);
        }));

        /**
         * @internal Translation (plural)
         * @see LocaleService::init()
         */
        $this->environment->addFunction(new TwigFunction("n__", function (string $original, string $plural, string $value){
            return n__($original, $plural, $value);
        }));

        /**
         * @example {{ asset("img/logo.png") }}
         */
        $this->environment->addFunction(new TwigFunction("asset", function (string $file){
            return sprintf("%s%s", $this->assetBaseDir, $file);
        }));

        return $this->environment;
    }
}