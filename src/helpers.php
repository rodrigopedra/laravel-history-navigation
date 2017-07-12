<?php

/**
 * @param  string $default
 *
 * @return string
 */
function navigate_back( $default = '/' )
{
    if (request( 'use_default' ) === '1') {
        return $default;
    }

    return route( 'navigate.back', compact( 'default' ) );
}
