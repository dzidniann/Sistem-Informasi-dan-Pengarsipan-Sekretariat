<script>
    (() => {
        const shell = document.getElementById('appShell');
        const sidebar = shell?.querySelector('.sidebar');
        const toggle = shell?.querySelector('.sidebar-toggle');
        if (!shell || !sidebar || !toggle) return;

        const mobile = window.matchMedia('(max-width: 980px)');
        let returnFocus = null;
        sidebar.id = 'appSidebar';
        sidebar.setAttribute('aria-label', 'Navigasi utama');
        sidebar.tabIndex = -1;
        toggle.setAttribute('aria-controls', sidebar.id);

        const synchronize = () => {
            const open = mobile.matches
                ? shell.classList.contains('sidebar-open')
                : !shell.classList.contains('sidebar-collapsed');
            toggle.setAttribute('aria-expanded', String(open));
            sidebar.inert = !open;
            document.body.classList.toggle('sidebar-modal-open', mobile.matches && open);
        };

        window.closeSidebarMobile = () => {
            shell.classList.remove('sidebar-open');
            if (mobile.matches && sidebar.contains(document.activeElement)) {
                (returnFocus?.isConnected ? returnFocus : toggle).focus();
            }
            synchronize();
        };

        window.toggleSidebar = () => {
            if (!mobile.matches) {
                shell.classList.toggle('sidebar-collapsed');
                synchronize();
                return;
            }
            if (shell.classList.contains('sidebar-open')) {
                window.closeSidebarMobile();
                return;
            }
            returnFocus = document.activeElement;
            shell.classList.add('sidebar-open');
            synchronize();
            sidebar.querySelector('.sidebar-close')?.focus();
        };

        document.addEventListener('keydown', event => {
            if (!mobile.matches || !shell.classList.contains('sidebar-open')) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                window.closeSidebarMobile();
            } else if (event.key === 'Tab') {
                const controls = [...sidebar.querySelectorAll('a[href], button, input, select, textarea, [tabindex="0"]')]
                    .filter(element => !element.disabled && element.getClientRects().length);
                const first = controls[0];
                const last = controls[controls.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last?.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first?.focus();
                }
            }
        });

        sidebar.addEventListener('click', event => {
            if (mobile.matches && event.target.closest('a[href]')) window.closeSidebarMobile();
        });

        mobile.addEventListener('change', () => {
            shell.classList.remove('sidebar-open');
            if (sidebar.contains(document.activeElement)) toggle.focus();
            synchronize();
        });

        shell.querySelectorAll('.table-wrapper').forEach(wrapper => {
            if (!wrapper.hasAttribute('tabindex')) wrapper.tabIndex = 0;
            if (!wrapper.hasAttribute('aria-label')) {
                wrapper.setAttribute('aria-label', 'Tabel data, geser ke samping untuk melihat kolom lainnya');
            }
            wrapper.setAttribute('role', 'region');
        });

        synchronize();
    })();
</script>
