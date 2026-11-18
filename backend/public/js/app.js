// Bootstrap Web Admin: markup sudah dirender Blade.
// JS hanya memasang sidebar/logout, event halaman, dan refresh diam-diam.

import { showPageError, runSilentRefresh } from './ui.js';
import { initLogin } from './pages/login.js';
import { initDashboard } from './pages/dashboard.js';
import { initAssets } from './pages/assets.js';
import { initCategories } from './pages/categories.js';
import { initLoans } from './pages/loans.js';

const pages = {
    dashboard: initDashboard,
    assets: initAssets,
    categories: initCategories,
    loans: initLoans,
};

function setupShell() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const toggles = [
        document.getElementById('menu-toggle'),
        document.getElementById('mobile-menu-toggle'),
    ].filter(Boolean);

    const isMobile = () => window.matchMedia('(max-width: 860px)').matches;

    if (localStorage.getItem('assetapp.sidebar') === 'collapsed') {
        sidebar?.classList.add('collapsed');
    }

    const closeMobile = () => {
        sidebar?.classList.remove('open');
        backdrop?.classList.remove('show');
    };

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            if (!sidebar) return;

            if (isMobile()) {
                const open = !sidebar.classList.contains('open');
                sidebar.classList.toggle('open', open);
                backdrop?.classList.toggle('show', open);
                return;
            }

            const collapsed = !sidebar.classList.contains('collapsed');
            sidebar.classList.toggle('collapsed', collapsed);
            localStorage.setItem('assetapp.sidebar', collapsed ? 'collapsed' : 'open');

            const label = collapsed ? 'Buka menu' : 'Tutup menu';
            toggles.forEach((button) => {
                if (button.id === 'menu-toggle') {
                    button.title = label;
                    button.setAttribute('aria-label', label);
                }
            });
        });
    });

    backdrop?.addEventListener('click', closeMobile);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeMobile();
    });
    document.querySelectorAll('.nav-item').forEach((link) => {
        link.addEventListener('click', closeMobile);
    });

    const logout = document.getElementById('btn-logout');
    if (logout) {
        logout.addEventListener('click', () => {
            logout.disabled = true;
            // Form Blade memuat @csrf; session diinvalidasi di server.
            document.getElementById('logout-form')?.submit();
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('page-root');
    const page = root?.dataset.page;

    document.addEventListener('visibilitychange', runSilentRefresh);

    if (page === 'login') {
        initLogin();
        return;
    }

    setupShell();

    const initializer = pages[page];
    if (initializer) {
        // Initializer hanya menyentuh selector spesifik, tidak menimpa markup Blade.
        Promise.resolve(initializer()).catch((error) => {
            showPageError(root, error, () => window.location.reload());
        });
    }
});
