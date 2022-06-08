<?php

namespace RodrigoPedra\HistoryNavigation;

if (! function_exists('value_or_null')) {
    /**
     * Return the default value of the given value or null if the value is empty.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value_or_null($value)
    {
        $value = value($value);

        if (is_string($value)) {
            $value = trim($value);
        }

        if (blank($value)) {
            return null;
        }

        return $value;
    }
}
