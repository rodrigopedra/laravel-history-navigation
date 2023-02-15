<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;

function navigate_back(string $default = '/'): string
{
    return Container::getInstance()->make(UrlGenerator::class)
        ->route('navigate.back', ['default' => $default]);
}

function navigate_default(string $default = '/'): string
{
    $request = Container::getInstance()->make(Request::class);

    if ($request->query->has('use_default')) {
        return $default;
    }

    return \navigate_back($default);
}
