<?php

/**
 * @param  string $default
 *
 * @return string
 */
function back_to( $default = '/' )
{
    if (request( 'go_back' ) === '1') {
        return $default;
    }

    return route( 'navigate.back', compact( 'default' ) );
}
