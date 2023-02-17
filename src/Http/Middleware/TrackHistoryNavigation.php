<?php

namespace RodrigoPedra\HistoryNavigation\Http\Middleware;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RodrigoPedra\HistoryNavigation\HistoryNavigationService;

class TrackHistoryNavigation
{
    public function __construct(
        private readonly HistoryNavigationService $historyService,
    ) {}

    public function handle(Request $request, \Closure $next): Response|RedirectResponse
    {
        if ($this->shouldIgnore($request)) {
            return $next($request);
        }

        return \tap($next($request), function (Response|RedirectResponse $response) use ($request) {
            if ($response->isSuccessful()) {
                $this->historyService->push($request->fullUrl())->persist();
            }
        });
    }

    private function shouldIgnore(Request $request): bool
    {
        return ! $request->isMethod('GET')
            || $request->ajax()
            || $request->wantsJson();
    }
}
