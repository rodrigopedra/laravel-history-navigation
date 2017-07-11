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
    private $default;

    /** @var  int */
    private $limit;

    /** @var  array */
    private $skipHistory;

    public function __construct(
        Request $request,
        UrlGenerator $urlGenerator,
        $default,
        $limit,
        array $skipHistory = []
    ) {
        $this->request      = $request;
        $this->urlGenerator = $urlGenerator;
        $this->history      = [];
        $this->default      = $urlGenerator->to( $default );
        $this->limit        = intval( preg_replace( '/\D/', '', $limit ) ?: 0 );
        $this->default      = $default;
        $this->limit        = $limit;
        $this->skipHistory  = $skipHistory;
    }

    public function peek()
    {
        return reset( $this->history ) ?: $this->default;
    }

    public function push( $url )
    {
        $url = $this->urlGenerator->to( $url );

        if ($url === $this->peek()) {
            return $this;
        }

        if (Str::is( '*/navigate/*', $url )) {
            return $this;
        }

        foreach ($this->skipHistory as $pattern) {
            if (preg_match( $pattern, $url ) === 1) {
                return $this;
            }
        }

        array_unshift( $this->history, $url );

        return $this;
    }

    public function pop( $default = '/' )
    {
        $default = !$default ? $this->default : $this->urlGenerator->to( $default );

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
}
