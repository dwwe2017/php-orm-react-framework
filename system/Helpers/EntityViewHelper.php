<?php

namespace Helpers;


use DateTime;
use Doctrine\ORM\EntityManager;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class ViewHelper
 * @package Helpers
 */
class EntityViewHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * ViewHelper constructor.
     * @param EntityManager $entityManager
     */
    private function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param EntityManager $entityManager
     * @return EntityViewHelper|null
     */
    public static final function init(EntityManager $entityManager)
    {
        if (is_null(self::$instance) || serialize($entityManager->getConnection()->getParams()) !== self::$instanceKey) {
            self::$instance = new self($entityManager);
            self::$instanceKey = serialize($entityManager->getConnection()->getParams());
        }

        return self::$instance;
    }

    /**
     * @param mixed $entities
     * @param string $className
     * @param string $icon
     * @param bool $checkbox_column
     * @param bool $toolbar
     * @param array $addModuleControllerAction
     * @param array $editModuleControllerAction
     * @param array $deleteModuleControllerAction
     * @return array
     * @example $this->getResponsiveTableArrayFromEntity(
     *  $users,
     *  Entities\Users::class,
     *  "icon-reorder",
     *  true, true,
     *  ["module", "controller", "editAction"],
     *  ["module", "controller", "deleteAction"]
     * );
     */
    public final function getResponsiveTableArrayFromEntity(string $className, $entities = null, $icon = "icon-reorder", $checkbox_column = true, $toolbar = true, array $addModuleControllerAction = array(), array $editModuleControllerAction = array(), array $deleteModuleControllerAction = array())
    {
        $thead = array();
        $tbody = array();

        if ($checkbox_column) {
            $thead[] = [
                "checkbox" => true
            ];
        }

        $meta = $this->entityManager->getClassMetadata($className);

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

        $entities = is_null($entities) ? $this->entityManager->getRepository($className)->findAll() : $entities;

        foreach ($entities as $key => $item) {
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
                } elseif (is_object($content) && method_exists($content, "getName")) {
                    $tbody[$key][] = [
                        "content" => htmlentities($content->getName())
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

        $result = [
            "heading" => $heading,
            "toolbar" => $toolbar ? [
                "collapse" => true,
                "refresh" => false,
                "manage" => $checkbox_column
            ] : false,
            "thead" => $thead,
            "tbody" => $tbody,
            "buttons" => !empty($editModuleControllerAction)
                || !empty($deleteModuleControllerAction)
        ];

        /**
         * If the array for the parameters of the link are not empty, the GET-Url will be created automatically
         */
        if (!empty($addModuleControllerAction)) {
            $module = $addModuleControllerAction[0] ?? null;
            $controller = $addModuleControllerAction[1] ?? null;
            $action = $addModuleControllerAction[2] ?? null;
            $result["toolbar"]["add"] = sprintf("?module=%s&controller=%s&action=%s", $module, $controller, $action);
        }

        return $result;
    }
}
