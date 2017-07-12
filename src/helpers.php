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

if (!function_exists( 'value_or_null' )) {
    /**
     * Return the default value of the given value or null if the value is empty.
     *
     * @param  mixed $value
     *
     * @return mixed
     */
    function value_or_null( $value )
    {
        $value = value( $value );

        if (is_object( $value )) {
            return $value;
        }

        if (is_array( $value )) {
            return empty( $value ) ? null : $value;
        }

        $value = trim( $value );

        if (empty( $value ) || !$value) {
            return null;
        }

        return $value;
    }
}
