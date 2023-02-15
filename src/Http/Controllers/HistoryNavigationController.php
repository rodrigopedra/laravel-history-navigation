<?php

namespace RodrigoPedra\HistoryNavigation\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use RodrigoPedra\HistoryNavigation\HistoryNavigationService;

class HistoryNavigationController
{
    public function __invoke(
        HistoryNavigationService $historyService,
        UrlGenerator $urlGenerator,
        ResponseFactory $responseFactory,
        Request $request,
    ): RedirectResponse {
        $request->session()->reflash();

        return $responseFactory->redirectTo(
            $historyService->previous($request->query('default', '/')),
            RedirectResponse::HTTP_FOUND,
            ['Cache-Control' => 'no-cache'],
        )->withInput();
    }
}
