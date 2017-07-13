<?php

namespace RodrigoPedra\HistoryNavigation;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\UrlGenerator;

class HistoryNavigationService
{
    const SESSION_KEY = 'navigation-history';

    /** @var  Request */
    private $request;

    /** @var  UrlGenerator */
    private $urlGenerator;

    /** @var  array */
    private $history;

    /** @var  string */
    private $globalDefault;

    /** @var  int */
    private $limit;

    /** @var  array */
    private $skipPatternsList;

    /** @var  boolean */
    private $removeEmptyQueryParameters;

    /** @var  array */
    private $ignoreQueryParametersList;

    public function __construct( Request $request, UrlGenerator $urlGenerator, array $config = [] )
    {
        $this->request      = $request;
        $this->urlGenerator = $urlGenerator;

        $this->history = [];

        $this->parseConfig( $config );
    }

    public function peek()
    {
        return reset( $this->history ) ?: $this->globalDefault;
    }

    public function push( $url )
    {
        $url = $this->parseUrl( $url );

        if (Str::is( '*/navigate/*', $url )) {
            return $this;
        }

        if ($url === $this->peek()) {
            return $this;
        }

        foreach ($this->skipPatternsList as $pattern) {
            if (preg_match( $pattern, $url ) === 1) {
                return $this;
            }
        }

        array_unshift( $this->history, $url );

        return $this;
    }

    public function pop( $default = '/' )
    {
        $default = !$default ? $this->globalDefault : $this->urlGenerator->to( $default );

        return array_shift( $this->history ) ?: $default;
    }

    public function clear()
    {
        $this->history = [];

        return $this;
    }

    public function boot()
    {
        if (!$this->request->hasSession()) {
            return $this;
        }

        $this->history = (array)$this->request->session()->get( self::SESSION_KEY, [] );

        return $this;
    }

    public function persist()
    {
        if (!$this->request->hasSession()) {
            return $this;
        }

        $this->request->session()->setPreviousUrl( $this->peek() );

        $this->request->session()->put( self::SESSION_KEY, array_slice( $this->history, 0, $this->limit ) );

        return $this;
    }

    public function parseUrl( $url )
    {
        $url = $this->urlGenerator->to( $url );

        $components = parse_url( $url );

        parse_str( array_get( $components, 'query', '' ), $queryParameters );

        $queryParameters = array_filter( $queryParameters, function ( $value, $key ) {
            if ($this->removeEmptyQueryParameters && is_null( value_or_null( $value ) )) {
                return false;
            }

            if (in_array( $key, $this->ignoreQueryParametersList )) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH );

        array_set( $components, 'query', http_build_query( $queryParameters ) );

        return http_build_url( $url, $components );
    }

    private function parseConfig( array $config = [] )
    {
        $this->globalDefault = $this->parseUrl( array_get( $config, 'default-url', '/' ) );

        $this->limit = intval( preg_replace( '/\D/', '', array_get( $config, 'limit', 50 ) ) );

        $this->skipPatternsList = (array)array_get( $config, 'skip-patterns', [] );

        $this->removeEmptyQueryParameters = (bool)array_get( $config, 'query.remove-empty', true );

        $this->ignoreQueryParametersList = (array)array_get( $config, 'query.ignore-parameters', [ 'page' ] );
    }
}
