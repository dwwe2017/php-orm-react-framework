<?php


namespace Helpers;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class EntityHelper
 * @package Helpers
 */
class EntityHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * EntityHelper constructor.
     * @param EntityManager $entityManager
     */
    private function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param EntityManager $entityManager
     * @return EntityHelper|null
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
     * @param string $className
     * @return ClassMetadata
     */
    public function getClassMetaData(string $className)
    {
        return $this->entityManager->getClassMetadata($className);
    }

    /**
     * @param string $className
     * @return array
     */
    public function getFieldNames(string $className)
    {
        return $this->getClassMetaData($className)->getFieldNames();
    }

    /**
     * @param string $className
     * @return array
     */
    public function getGetterFieldNames(string $className)
    {
        $result = [];
        $fieldNames = $this->getFieldNames($className);

        foreach ($fieldNames as $fieldName) {
            $getter = sprintf("get%s", ucfirst(StringHelper::init($fieldName)->camelize()->getString()));
            if (!method_exists($className, $getter)) {
                continue;
            }

            $result[] = $fieldName;
        }

        return $result;
    }

    /**
     * @param string $className
     * @param bool $sort
     * @param array $attrs
     * @return array
     */
    public function getGetterFieldNamesColumn(string $className, $sort = true, $attrs = ["data-class" => "expand"])
    {
        $result = [];
        $getterFields = $this->getGetterFieldNames($className);

        foreach ($getterFields as $getterField) {
            $result[] = [
                "dataField" => $getterField,
                "text" => StringHelper::init($getterField)->decamelize()->ucFirst()->getString(),
                "sort" => $sort,
                "attrs" => $attrs
            ];
        }

        return $result;
    }

    /**
     * @param string $className
     * @return array
     */
    public function getGetterMethods(string $className)
    {
        $result = [];
        $fieldNames = $this->getFieldNames($className);

        foreach ($fieldNames as $fieldName) {
            $getter = sprintf("get%s", ucfirst(StringHelper::init($fieldName)->camelize()->getString()));
            if (!method_exists($className, $getter)) {
                continue;
            }

            $result[$fieldName] = $getter;
        }

        return $result;
    }
}
