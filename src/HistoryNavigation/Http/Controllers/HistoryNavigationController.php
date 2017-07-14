<?php

namespace RodrigoPedra\HistoryNavigation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Routing\Controller as BaseController;
use RodrigoPedra\HistoryNavigation\HistoryNavigationService;

class HistoryNavigationController extends BaseController
{
    /** @var HistoryNavigationService */
    private $historyService;

    /** @var UrlGenerator */
    private $urlGenerator;

    /** @var ResponseFactory */
    private $responseFactory;

    public function __construct(
        HistoryNavigationService $historyService,
        UrlGenerator $urlGenerator,
        ResponseFactory $responseFactory
    ) {
        $this->middleware( 'web' );

        $this->historyService  = $historyService;
        $this->urlGenerator    = $urlGenerator;
        $this->responseFactory = $responseFactory;
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

        return $this->responseFactory->redirectTo( $to )->withInput();
    }

    public function sync( Request $request )
    {
        $this->historyService->boot();

        $current  = $request->get( 'current' );
        $referrer = $request->get( 'referrer' );

        $current  = $this->historyService->parseUrl( $current );
        $referrer = $this->historyService->parseUrl( $referrer );

        // window refresh
        if ($current === $referrer) {
            return $this->responseFactory->make( $this->historyService->previous(), 200, [
                'Cache-Control' => 'no-cache',
            ] );
        }

        while ($this->historyService->count() && $referrer === $this->historyService->peek()) {
            $this->historyService->pop();
        }

        $this->historyService->push( $current );

        $this->historyService->persist();

        return $this->responseFactory->make( $this->historyService->previous(), 200, [
            'Cache-Control' => 'no-cache',
        ] );
    }
}
