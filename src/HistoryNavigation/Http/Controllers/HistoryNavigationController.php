<?php

namespace RodrigoPedra\HistoryNavigation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\Controller as BaseController;
use RodrigoPedra\HistoryNavigation\HistoryNavigationService;

class HistoryNavigationController extends BaseController
{
    /** @var HistoryNavigationService */

    private $historyService;

    /** @var UrlGenerator */
    private $urlGenerator;

    public function __construct( HistoryNavigationService $historyService, UrlGenerator $urlGenerator )
    {
        $this->middleware( 'web' );

        $this->historyService = $historyService;
        $this->urlGenerator   = $urlGenerator;
    }

    public function back( Request $request )
    {
        $default  = $request->query( 'default', '/' );
        $previous = $this->urlGenerator->previous( $default );

        $default  = $this->historyService->parseUrl( $default );
        $previous = $this->historyService->parseUrl( $previous );

        $to = $this->historyService->pop( $default );

        if ($to === $previous) {
            $to = $this->historyService->pop( $default );
        }

        $request->session()->reflash();

        return redirect()->to( $to )->withInput();
    }
}
