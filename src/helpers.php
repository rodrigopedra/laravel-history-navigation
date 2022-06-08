<?php

/**
 * @param  string  $default
 * @return string
 */
function navigate_back($default = '/')
{
    return route('navigate.back', ['default' => $default]);
}

/**
 * @param  string  $default
 * @return string
 */
function navigate_default($default = '/')
{
    if (request('use_default') === '1') {
        return $default;
    }

    return route('navigate.back', ['default' => $default]);
}
