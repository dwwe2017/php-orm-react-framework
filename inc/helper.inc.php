<?php
/**
 * View Helper functions
 */


/**
 * Default view helper function for user input to be reissued or saved
 * @param $string
 * @param string $encoding
 * @return string
 */
function clean($string, $encoding = "UTF-8")
{
    return htmlspecialchars(
        strip_tags($string),
        ENT_QUOTES|ENT_HTML5,
        $encoding
    );
}

/**
 * For output of user input that should support HTML code
 * @param $string
 * @return string
 */
function purify($string)
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