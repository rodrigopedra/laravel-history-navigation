<script>
    window.history && window.history.pushState( {}, '', window.document.URL );

    window.onpopstate = function () {
        window.location.href = '{{ route('navigate.back') }}?_=' + (+(new Date()));
    };
</script>
