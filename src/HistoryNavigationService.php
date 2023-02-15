<?php

namespace RodrigoPedra\HistoryNavigation;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class HistoryNavigationService
{
    public const SESSION_KEY = 'navigation-history';

    private \SplQueue $history;
    private readonly int $limit;

    public function __construct(
        private readonly UrlGenerator $url,
        private readonly ?Session $session,
        private readonly string $defaultUrl,
        private readonly array $skipPatternsList,
        private readonly bool $removeEmptyQueryParameters,
        private readonly array $ignoreQueryParametersList,
        int $limit,
    ) {
        $this->limit = \max(1, $limit);
        $this->history = new \SplQueue();

        $history = $this->session?->get(self::SESSION_KEY, []) ?? [];
        $history = Arr::flatten(Arr::wrap($history));

        foreach ($history as $entry) {
            if (\filter_var($entry, FILTER_VALIDATE_URL) !== false) {
                $this->history->push($entry);
            }
        }
    }

    public function push($url): self
    {
        $url = $this->parseUrl($url);

        if (Str::is('*/navigate/back', $url)) {
            return $this;
        }

        if ($url === $this->peek()) {
            return $this;
        }

        foreach ($this->skipPatternsList as $pattern) {
            if (\preg_match($pattern, $url) === 1) {
                return $this;
            }
        }

        $this->history->push($url);

        while ($this->history->count() > $this->limit) {
            $this->history->shift();
        }

        return $this;
    }

    public function pop(?string $default = '/'): string
    {
        if ($this->history->isEmpty()) {
            return $this->parseUrl($default ?? '/');
        }

        return $this->history->pop();
    }

    public function peek(?string $default = '/'): string
    {
        if ($this->history->isEmpty()) {
            return $this->parseUrl($default ?? '/');
        }

        return $this->history->top();
    }

    public function previous(?string $default = '/'): string
    {
        $default = $this->parseUrl($default ?? '/');

        $previous = $this->url->previous($default);
        $previous = $this->parseUrl($previous);

        do {
            if ($this->history->isEmpty()) {
                return $default;
            }

            $to = $this->pop($default);
        } while ($to === $previous);

        return $to;
    }

    public function clear(): self
    {
        $this->history = new \SplQueue();

        return $this;
    }

    public function persist(): self
    {
        if (! $this->session) {
            return $this;
        }

        $this->session->setPreviousUrl($this->peek() ?? $this->url->previous('/'));
        $this->session->put(self::SESSION_KEY, \iterator_to_array($this->history));

        return $this;
    }

    private function parseUrl(?string $url): string
    {
        if (\blank($url)) {
            $url = $this->defaultUrl;
        }

        $url = $this->url->to($url);

        $components = \parse_url($url);

        \parse_str(Arr::get($components, 'query', ''), $queryParameters);

        $queryParameters = \array_filter($queryParameters, function ($value, $key) {
            if ($this->removeEmptyQueryParameters && \blank($value)) {
                return false;
            }

            if (\in_array($key, $this->ignoreQueryParametersList)) {
                return false;
            }

            return true;
        }, \ARRAY_FILTER_USE_BOTH);

        Arr::set($components, 'query', \http_build_query($queryParameters));

        return \http_build_url($url, $components);
    }
}
