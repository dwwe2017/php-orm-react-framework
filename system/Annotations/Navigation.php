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

namespace Annotations;


use Doctrine\Common\Annotations\Annotation\Enum;

/**
 * Class Access
 * @package Annotations
 * @Annotation
 * @Target("CLASS")
 */
class Navigation
{
    /**
     * @var string
     * @Enum({"sidebar", "top_left", "top_right", "misc"})
     */
    public string $position;

    /**
     * @var string
     */
    public string $text;

    /**
     * @var string
     */
    public string $class;

    /**
     * @var string
     */
    public string $icon = "icon-angle-right";

    /**
     * @var string
     */
    public string $title;

    /**
     * @var bool
     */
    public bool $hidden;

    /**
     * @var string
     */
    public string $badge;

    /**
     * @var string
     * @Enum({"info","success","warning","danger"})
     */
    public string $badgeClass;

    /**
     * @var array
     */
    public array $requiredGetParams;

    /**
     * @var bool
     */
    public bool $isLabel;

    /**
     * @var string
     * @Enum({"info", "success", "warning", "danger"})
     */
    public string $labelClass;

    /**
     * @var string
     */
    public string $labelIcon;

    /**
     * @var string
     */
    public string $style;

    /**
     * @var string
     */
    public string $description;

    /**
     * @var string
     */
    public string $href = "javascript:void(0)";

    /**
     * @var string
     * @Enum({"_blank", "_self", "_parent"})
     */
    public string $target;
}