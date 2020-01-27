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
    private string $dir = "";

    /**
     * @var string|null
     */
    private ?string $exceptionClass = null;

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

    /**
     * @return bool
     */
    public function addDirectoryProtection()
    {
        FileHelper::init($this->dir)->isWritable(true);
        $htaccess = sprintf("%s/.htaccess", $this->dir);

        if(file_exists($htaccess)){
           return true;
        }

        return @file_put_contents($htaccess, "# Apache 2.2
<IfModule !authz_core_module>
	Order deny,allow
    Deny from all
</IfModule>

# Apache 2.4+
<IfModule authz_core_module>
	<RequireAll>
		Require all denied
	</RequireAll>
</IfModule>") !== false;
    }

    /**
     * @param string $allowed_files_types_regex
     * @param bool $with_http_auth_rewrite
     * @return bool
     */
    public function addDirectoryRestriction($allowed_files_types_regex = "^|index\.php|\.(js|css|gif|jpeg|jpg|png|woff|svg)", $with_http_auth_rewrite = false)
    {
        FileHelper::init($this->dir)->isWritable(true);
        $htaccess = sprintf("%s/.htaccess", $this->dir);

        if(file_exists($htaccess) || empty($allowed_files_types_regex)){
            return true;
        }

        return @file_put_contents($htaccess, sprintf("# Prevent unauthorized access to non-user content
<IfModule mod_rewrite.c>
    RewriteEngine On%s
    RewriteRule !(%s)$ - [L,R=403]
</IfModule>", $with_http_auth_rewrite ? "\n\tRewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]" : "",
                    is_array($allowed_files_types_regex)
                ? sprintf("\.(%s)", implode("|", $allowed_files_types_regex))
                : $allowed_files_types_regex)
            ) !== false;
    }
}
