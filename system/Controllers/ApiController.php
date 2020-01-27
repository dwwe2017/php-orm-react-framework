<?php

namespace Controllers;

use Gettext\Translation;
use Interfaces\ControllerInterfaces\XmlControllerInterface;

/**
 * Class ApiController
 * @package Controllers
 */
class ApiController extends RestrictedXmlController implements XmlControllerInterface
{
    /**
     *
     */
    public function indexAction(): void
    {
        parent::indexAction();
    }

    /**
     * @param string $default
     * @internal Returns a JSON array for example. to request a translation for Ajax scripts
     * using Ajax. Can be easily integrated in the Kinde controller using parent::getTranslationAction()
     */
    protected function getTranslationAction(string $default = "en_US"): void
    {
        $this->contextClear();
        $langCode = $this->getRequestHandler()->getQuery()->get("langCode", $default);
        foreach ($this->getTranslations($langCode)->getArrayCopy() as $key => $item) {
            if ($item instanceof Translation) {
                $this->addContext($item->getOriginal(), $item->getTranslation());
            }
        }
    }
}