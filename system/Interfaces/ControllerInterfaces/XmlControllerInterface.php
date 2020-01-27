<?php

namespace Interfaces\ControllerInterfaces;

/**
 * Interface XmlControllerInterface
 * @package Interfaces\ControllerInterfaces
 */
interface XmlControllerInterface
{
    const HEADER_CONTENT_TYPE_JSON = "Content-type: application/json; charset=utf-8";

    const HEADER_ERROR_404 = "HTTP/1.0 404 Not Found";

    const HEADER_ERROR_403 = "HTTP/1.0 403 Forbidden";

    public function indexAction(): void;
}