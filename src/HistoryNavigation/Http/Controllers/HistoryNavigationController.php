<?php

namespace RodrigoPedra\HistoryNavigation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\Controller as BaseController;
use RodrigoPedra\HistoryNavigation\HistoryNavigationService;

class HistoryNavigationController extends BaseController
{
    /** @var HistoryNavigationService */

    private $historyService;

    /** @var UrlGenerator */
    private $urlGenerator;

    /** @var Redirector */
    private $redirector;

    public function __construct(
        HistoryNavigationService $historyService,
        UrlGenerator $urlGenerator,
        Redirector $redirector
    ) {
        $this->middleware( 'web' );

        $this->historyService = $historyService;
        $this->urlGenerator   = $urlGenerator;
        $this->redirector     = $redirector;
    }

    public function back( Request $request )
    {
        $default  = $request->query( 'default', '/' );
        $previous = $this->urlGenerator->previous( $default );

        $default  = $this->historyService->parseUrl( $default );
        $previous = $this->historyService->parseUrl( $previous );

        $to = $this->historyService->pop( $default );

        while ($to === $previous) {
            $to = $this->historyService->pop( $default );

            if ($to === $previous && $this->historyService->count() === 0) {
                $to = $default;
                break;
            }
        }

        $request->session()->reflash();

        return $this->redirector->to( $to )->withInput();
    }

    public function sync( Request $request )
    {
        $this->historyService->boot();

        $referrer = $request->get( 'referrer' );

        $log = [];

        if (!is_null( $referrer )) {
            $referrer = $this->historyService->parseUrl( $referrer );
            $previous = $this->historyService->peek();

            while ($referrer === $previous && $this->historyService->count()) {
                $log[]    = $previous;
                $previous = $this->historyService->pop();
            }
        }

        $current = $request->get( 'current' );

        if (!is_null( $current )) {
            $this->historyService->push( $current );
        }

        $this->historyService->persist();

        return 'OK';
    }
}
