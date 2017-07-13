<?php

namespace RodrigoPedra\HistoryNavigation\Http\Middleware;

use Closure;
use RodrigoPedra\HistoryNavigation\HistoryNavigationService;

class TrackHistoryNavigation
{
    /** @var HistoryNavigationService */
    private $history;

    public function __construct( HistoryNavigationService $historyService )
    {
        $this->history = $historyService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        if (!$request->isMethod( 'GET' )) {
            return $next( $request );
        }

        $this->history->boot();

        /** @var \Illuminate\Http\Response $response */
        $response = $next( $request );

        if (!$response->isRedirection()) {
            $this->history->push( $request->fullUrl() );
        }

        $this->history->persist();

        return $response;
    }
}
