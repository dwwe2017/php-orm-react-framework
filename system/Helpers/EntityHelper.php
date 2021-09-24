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
    private EntityManager $entityManager;

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
    public static final function init(EntityManager $entityManager): ?EntityHelper
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
    public function getClassMetaData(string $className): ClassMetadata
    {
        return $this->entityManager->getClassMetadata($className);
    }

    /**
     * @param string $className
     * @return array
     */
    public function getFieldNames(string $className): array
    {
        return $this->getClassMetaData($className)->getFieldNames();
    }

    /**
     * @param string $className
     * @return array
     */
    public function getGetterFieldNames(string $className): array
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
    public function getGetterFieldNamesColumn(string $className, bool $sort = true, array $attrs = ["data-class" => "expand"]): array
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
     * @param array $exclude
     * @return array
     */
    public function getGetterMethods(string $className, array $exclude = []): array
    {
        $result = [];
        $fieldNames = $this->getFieldNames($className);

        foreach ($fieldNames as $fieldName) {
            if(in_array($fieldName, $exclude)){
                continue;
            }

            $getter = sprintf("get%s", ucfirst(StringHelper::init($fieldName)->camelize()->getString()));
            if (!method_exists($className, $getter)) {
                continue;
            }

            $result[$fieldName] = $getter;
        }

        return $result;
    }
}
