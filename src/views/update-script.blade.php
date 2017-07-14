<script>
    window.onpopstate = function () {
        var url = '{{ app(\RodrigoPedra\HistoryNavigation\HistoryNavigationService::class)->peek() }}';

        window.history.pushState( url, '', url );
    };

    window.document.addEventListener( 'DOMContentLoaded', function () {
        var SESSION_STORAGE_KEY = '{{session('navigation-history.javascript', str_random())}}';

        if ( !window.sessionStorage || !window.XMLHttpRequest || !window.FormData ) {
            return;
        }

        // @see https://stackoverflow.com/a/11767598/1211472
        var getCookie = function getCookie ( name ) {
            // Get name followed by anything except a semicolon
            var cookiestring = (new RegExp( "" + name + "[^;]+" )).exec( window.document.cookie );
            // Return everything after the equal sign, or an empty string if the cookie name not found
            return decodeURIComponent( !!cookiestring ? cookiestring.toString().replace( /^[^=]+./, "" ) : "" );
        };

        var current = window.location.href;
        var referrer = window.sessionStorage.getItem( SESSION_STORAGE_KEY );

        window.sessionStorage.setItem( SESSION_STORAGE_KEY, current );

        var request = new XMLHttpRequest();
        request.open( 'POST', '{{ route('navigate.sync') }}' );
        request.setRequestHeader( 'X-XSRF-TOKEN', getCookie( 'XSRF-TOKEN' ) );

        var formData = new FormData();

        formData.append( 'current', current );

        if ( referrer ) {
            formData.append( 'referrer', referrer );
        }

        request.send( formData );
    }, false );
</script>
