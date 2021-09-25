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

/**
 * View Helper functions
 */

/**
 * Default view helper function for user input to be reissued or saved
 * @param string $string
 * @param string $encoding
 * @return string
 */
function clean(string $string, string $encoding = "UTF-8"): string
{
    return htmlspecialchars(
        strip_tags($string),
        ENT_QUOTES|ENT_HTML5,
        $encoding
    );
}

/**
 * For output of user input that should support HTML code
 * @param string $string
 * @return string
 */
function purify(string $string): string
{
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);

    return $purifier->purify($string);
}

/**
 * For development purposes for sorted output of objects, etc.
 * @param $input
 */
function print_pre($input)
{
    print "<pre>";
    print_r($input);
    print "</pre>";
}