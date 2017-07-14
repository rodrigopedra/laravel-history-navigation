<script>
    window.history && window.history.pushState( {}, '', window.document.URL );

    window.onpopstate = function () {
        window.history && window.history.pushState( {}, '', window.document.URL );
    };
</script>
