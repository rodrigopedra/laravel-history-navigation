<script>
    window.document.addEventListener( 'DOMContentLoaded', function () {
        var SESSION_STORAGE_KEY = '{{session('navigation-history.javascript', str_random())}}';

        if ( !window.sessionStorage || !window.XMLHttpRequest || !window.FormData ) {
            return;
        }

        var lastUrl = { url : '{{ request()->fullUrl() }}', loaded : false };

        window.onpopstate = function () {
            console.log( 'onpopstate', lastUrl );

            if ( lastUrl.loaded ) {
                window.history && window.history.replaceState( {}, '', lastUrl.url );
            }

            window.history && window.history.back();
        };

        window.history && window.history.pushState( {}, '', lastUrl.url );

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

        var syncHystory = function syncHystory () {
            lastUrl.url = request.responseText;
            lastUrl.loaded = true;
        };

        request.addEventListener( 'load', syncHystory );
        request.open( 'POST', '{{ route('navigate.sync') }}?_=' + +(new Date()) );
        request.setRequestHeader( 'X-XSRF-TOKEN', getCookie( 'XSRF-TOKEN' ) );

        var formData = new FormData();

        formData.append( 'current', current );

        if ( referrer ) {
            formData.append( 'referrer', referrer );
        }

        request.send( formData );
    }, false );
</script>
