<script>
window.document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    if (!window.history || window.onpopstate !== null) {
        return;
    }

    window.history.pushState({}, '', window.document.location);

    window.onpopstate = function () {
        if (window.location.hash.length) {
            window.history.replaceState({}, '', window.document.location);
            return;
        }

        window.location.href = '{{ navigate_back() }}?_=' + (+(new Date()));
    };
});
</script>
