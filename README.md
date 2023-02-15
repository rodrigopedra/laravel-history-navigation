# Laravel History Navigation

History navigation inspired by the browser's History API

```bash
composer require rodrigopedra/laravel-history-navigation
```

- Version 1.0.0 supports Laravel 10.x (and as such, requires PHP 8.1)
- Version 0.9.x supports Laravel from version 6.x through 9.x

## Introduction

This package aims to mimic the browser's History API. 

It tries to automatically track the navigation history, and provides two global helpers 
to allow redirecting a user back on history.

The idea was to provide back history navigation to applications which do deep navigation, 
such as drill-down data dashboards, and doesn't always have a clear back path. 

## Usage

On a view you can use the `navigate_back()` helper as the `href` of a a back link/button:

```blade
<a href="{{ navigate_back('/') }}">Go back</a>
```

The parameter is a default destination, in case the history is empty.

There is also the `navigate_default()` helper, which redirects to the default route 
defined as its parameter, or defers to `navigate_back()` when this parameter is not present.

## Caveats

This package relies on the user's session, as such if a user has multiple tabs or windows opened 
and navigates within the same application, it will not work as expected as it will mix 
the different tabs/windows navigation history.

This package was developed with a very specific scenario in mind, for an intranet application 
that ran within a single window browser wrapper (similar to electron).

Nowadays, I do not recommend its adoption on new projects, and advise on using the browser's 
native History API.
