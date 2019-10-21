<?php


namespace Helpers;


use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class DirHelper
 * @package Helpers
 */
class DirHelper
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var string
     */
    private $dir = "";

    /**
     * @var string|null
     */
    private $exceptionClass = null;

    /**
     * DirHelper constructor.
     * @param string $dir
     * @param string|null $exceptionClass
     */
    private function __construct(string $dir, ?string $exceptionClass = null)
    {
        $this->dir = $dir;
        $this->exceptionClass = class_exists($exceptionClass) ? $exceptionClass : null;
    }

    /**
     * @param string $dir
     * @param string|null $exceptionClass
     * @return DirHelper|null
     */
    public static final function init(string $dir, ?string $exceptionClass = null)
    {
        if (is_null(self::$instance) || serialize($dir . $exceptionClass) !== self::$instanceKey) {
            self::$instance = new self($dir, $exceptionClass);
            self::$instanceKey = serialize($dir . $exceptionClass);
        }

        return self::$instance;
    }

    /**
     * @param array $filter
     * @param array $withOut
     * @return array
     */
    public function getScan(array $filter = [], array $withOut = [".", ".."])
    {
        if ($this->exceptionClass) {
            FileHelper::init($this->dir, $this->exceptionClass)->isReadable();
        }

        $result = [];
        foreach (scandir($this->dir) as $item) {
            if (in_array($item, $withOut)) {
                continue;
            } elseif (empty($filter)) {
                $result[] = $item;
            } else {
                foreach ($filter as $value) {
                    if (strpos($item, $value) !== false) {
                        $result[] = $item;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array $filter
     * @param array $withOut
     * @return string
     */
    public function getMd5CheckSum(array $filter = [], array $withOut = [".", ".."])
    {
        if ($this->exceptionClass) {
            FileHelper::init($this->dir, $this->exceptionClass)->isReadable();
        }

        $result = "";
        foreach (scandir($this->dir) as $item) {
            if (in_array($item, $withOut)) {
                continue;
            } elseif (empty($filter)) {
                $result .= md5_file(sprintf("%s/%s", $this->dir, $item));
            } else {
                foreach ($filter as $value) {
                    if (strpos($item, $value) !== false) {
                        $result .= md5_file(sprintf("%s/%s", $this->dir, $item));
                    }
                }
            }
        }

        return $result;
    }
}
