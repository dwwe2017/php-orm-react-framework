<?php


namespace Helpers;


use DateTime;
use Doctrine\ORM\EntityManager;
use Exceptions\FileFactoryException;
use Interfaces\EntityInterfaces\CustomEntityInterface;
use Traits\EntityTraits\CustomEntityTrait;

/**
 * Class ViewHelper
 * @package Helpers
 */
class ViewHelper
{
    /**
     * @param EntityManager $em
     * @param string $className
     * @param string $icon
     * @param bool $checkbox_column
     * @param bool $toolbar
     * @param array $editModuleControllerAction
     * @param array $deleteModuleControllerAction
     * @return array
     * @example $this->getResponsiveTableArrayFromEntity(
     *  $entityManager,
     *  Entities\Users::class,
     *  "icon-reorder",
     *  true, true,
     *  ["module", "controller", "editAction"],
     *  ["module", "controller", "deleteAction"]
     * );
     */
    public static function getResponsiveTableArrayFromEntity(EntityManager $em, string $className, $icon = "icon-reorder", $checkbox_column = true, $toolbar = true, array $editModuleControllerAction = array(), array $deleteModuleControllerAction = array())
    {
        $thead = array();
        $tbody = array();

        if ($checkbox_column) {
            $thead[] = [
                "checkbox" => true
            ];
        }

        $meta = $em->getClassMetadata($className);

        /**
         * @internal Here, it is checked whether the entity to be processed also contains or uses the corresponding traits and / or interfaces
         * @see CustomEntityInterface
         * @see CustomEntityTrait
         */
        $classHelper = ClassHelper::init($meta->getReflectionClass(), FileFactoryException::class);
        $classHelper->hasInterface(CustomEntityInterface::class);
        $classHelper->hasTrait(CustomEntityTrait::class);

        foreach ($meta->getFieldNames() as $fieldName) {
            $thead[md5($fieldName)] = [
                "title" => ucfirst($fieldName),
                "expand" => true
            ];
        }

        $headingText = StringHelper::init($meta->getName())->rmNamespace()->getString();

        $heading = [
            "text" => $headingText,
            "icon" => $icon
        ];

        $repo = $em->getRepository($className);
        foreach ($repo->findAll() as $key => $item) {
            if ($checkbox_column) {
                $tbody[$key][] = [
                    "checkbox" => method_exists($item, "getId") ? $item->getId() : "checkbox"
                ];
            }

            foreach ($meta->getFieldNames() as $fieldName) {
                $getter = sprintf("get%s", ucfirst(StringHelper::init($fieldName)->camelize()->getString()));
                if (!method_exists($item, $getter)) {
                    unset($thead[md5($fieldName)]);
                    continue;
                }

                $content = $item->{$getter}();
                if ($content instanceof DateTime) {
                    $tbody[$key][] = [
                        "content" => $content->format("d.m.Y")
                    ];
                } else {
                    $tbody[$key][] = [
                        "content" => htmlentities($content)
                    ];
                }
            }

            /**
             * The entity must contain the getId() getter so that buttons can be created
             */
            if (method_exists($item, "getId")) {
                $id = $item->getId();

                /**
                 * If the array for the parameters of the link are not empty, the GET-Url will be created automatically
                 */
                if (!empty($editModuleControllerAction)) {
                    $module = $editModuleControllerAction[0] ?? null;
                    $controller = $editModuleControllerAction[1] ?? null;
                    $action = $editModuleControllerAction[2] ?? null;
                    $tbody[$key]["buttons"]["edit"] = sprintf("?module=%s&controller=%s&action=%s&id=%s", $module, $controller, $action, $id);
                }

                /**
                 * If the array for the parameters of the link are not empty, the GET-Url will be created automatically
                 */
                if (!empty($deleteModuleControllerAction)) {
                    $module = $deleteModuleControllerAction[0] ?? null;
                    $controller = $deleteModuleControllerAction[1] ?? null;
                    $action = $deleteModuleControllerAction[2] ?? null;
                    $tbody[$key]["buttons"]["delete"] = sprintf("?module=%s&controller=%s&action=%s&id=%s", $module, $controller, $action, $id);
                }
            } else {
                $editModuleControllerAction = [];
                $deleteModuleControllerAction = [];
                $tbody[$key]["buttons"] = false;
            }
        }

        return [
            "heading" => $heading,
            "toolbar" => $toolbar,
            "thead" => $thead,
            "tbody" => $tbody,
            "buttons" => !empty($editModuleControllerAction)
                || !empty($deleteModuleControllerAction)
        ];
    }
}