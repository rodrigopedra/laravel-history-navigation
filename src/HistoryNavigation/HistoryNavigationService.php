<?php

namespace RodrigoPedra\HistoryNavigation;

use Illuminate\Support\Str;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Routing\UrlGenerator;

class HistoryNavigationService
{
    const SESSION_KEY = 'navigation-history';

    /** @var  Session */
    private $session;

    /** @var  UrlGenerator */
    private $urlGenerator;

    /** @var  array */
    private $history;

    /** @var  string */
    private $defaultUrl;

    /** @var  int */
    private $limit;

    /** @var  array */
    private $skipPatternsList;

    /** @var  boolean */
    private $removeEmptyQueryParameters;

    /** @var  array */
    private $ignoreQueryParametersList;

    /** @var  boolean */
    private $booted;

    public function __construct( UrlGenerator $urlGenerator, Session $session = null, array $config = [] )
    {
        $this->session      = $session;
        $this->urlGenerator = $urlGenerator;

        $this->history = [];
        $this->booted  = false;

        $this->parseConfig( $config );
    }

    public function peek()
    {
        return reset( $this->history ) ?: $this->defaultUrl;
    }

    public function push( $url )
    {
        $url = $this->parseUrl( $url );

        if (Str::is( '*/navigate/back', $url )) {
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
        $default = !$default ? $this->defaultUrl : $this->urlGenerator->to( $default );

        return array_shift( $this->history ) ?: $default;
    }

    public function clear()
    {
        $this->history = [];

        return $this;
    }

    public function count()
    {
        return count( $this->history );
    }

    public function boot()
    {
        if ($this->booted) {
            return $this;
        }

        if (is_null( $this->session )) {
            return $this;
        }

        $this->history = array_wrap( $this->session->get( self::SESSION_KEY, [] ) );
        $this->booted  = true;

        return $this;
    }

    public function persist()
    {
        if (is_null( $this->session )) {
            return $this;
        }

        $this->session->setPreviousUrl( $this->peek() );
        $this->session->put( self::SESSION_KEY, array_slice( $this->history, 0, $this->limit ) );

        return $this;
    }

    public function parseUrl( $url )
    {
        if (is_null( value_or_null( $url ) )) {
            $url = $this->defaultUrl;
        }

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
        $this->defaultUrl = $this->parseUrl( array_get( $config, 'default-url', '/' ) );

        $this->limit            = intval( preg_replace( '/\D/', '', array_get( $config, 'limit', 50 ) ) );
        $this->skipPatternsList = array_wrap( array_get( $config, 'skip-patterns', [] ) );

        $this->removeEmptyQueryParameters = (bool)array_get( $config, 'query.remove-empty', true );
        $this->ignoreQueryParametersList  = array_wrap( array_get( $config, 'query.ignore-parameters', [ 'page' ] ) );
    }
}
