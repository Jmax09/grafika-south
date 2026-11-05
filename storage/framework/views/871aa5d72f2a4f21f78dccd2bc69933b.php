
<script>
    (function () {
        var root = document.documentElement;
        var STORAGE_KEY = 'grafika-theme';
        var mql = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

        function readStored() {
            try { return localStorage.getItem(STORAGE_KEY); } catch (e) { return null; }
        }

        function syncToggles(dark) {
            var buttons = document.querySelectorAll('[data-theme-toggle]');
            for (var i = 0; i < buttons.length; i++) {
                var label = dark ? 'Switch to light theme' : 'Switch to dark theme';
                buttons[i].setAttribute('aria-pressed', dark ? 'true' : 'false');
                buttons[i].setAttribute('aria-label', label);
                buttons[i].setAttribute('title', label);
            }
        }

        function apply(mode) {
            var dark = mode === 'dark';
            root.classList.toggle('theme-dark', dark);
            root.style.colorScheme = dark ? 'dark' : 'light';
            syncToggles(dark);
        }

        // 1) Resolve the mode BEFORE first paint (no flash of the wrong theme).
        var stored = readStored();
        var prefersDark = mql ? mql.matches : false;
        apply(stored === 'dark' || (stored !== 'light' && prefersDark) ? 'dark' : 'light');

        // 1b) This script runs in <head>, before the toggle buttons exist.
        //     Re-sync their ARIA state once the DOM is parsed.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                syncToggles(root.classList.contains('theme-dark'));
            });
        } else {
            syncToggles(root.classList.contains('theme-dark'));
        }

        // 2) Toggle handler — delegated so it covers every button instance.
        document.addEventListener('click', function (e) {
            var button = e.target.closest ? e.target.closest('[data-theme-toggle]') : null;
            if (!button) return;
            var next = root.classList.contains('theme-dark') ? 'light' : 'dark';
            apply(next);
            try { localStorage.setItem(STORAGE_KEY, next); } catch (err) {}
        });

        // 3) Follow the OS preference only while the user has not chosen explicitly.
        if (mql && mql.addEventListener) {
            mql.addEventListener('change', function (e) {
                if (!readStored()) apply(e.matches ? 'dark' : 'light');
            });
        }
    })();
</script>
<?php /**PATH C:\xampp\htdocs\grafika-south\resources\views/partials/theme-script.blade.php ENDPATH**/ ?>