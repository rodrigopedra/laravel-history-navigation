<script>
    window.document.addEventListener( 'DOMContentLoaded', function () {
        'use strict';

        if ( !window.history || window.onpopstate !== null ) {
            return;
        }

        window.history.pushState( {}, '', window.document.URL );

        window.onpopstate = function () {
            window.location.href = '{{ navigate_back() }}?_=' + (+(new Date()));
        };
    } );
</script>
