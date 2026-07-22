<style>
    /* Tour Modal Animations */
    @keyframes tourBackdropFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes tourModalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes tourStepFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes tourPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    .tour-wrapper {
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: 9999;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tour-wrapper.tour-hidden {
        display: none !important;
        pointer-events: none;
    }

    .tour-wrapper.tour-visible {
        pointer-events: auto;
    }

    .tour-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(2px);
        z-index: 9998;
        animation: tourBackdropFadeIn 0.3s ease-out;
        pointer-events: auto;
    }

    .tour-modal {
        position: relative;
        z-index: 9999;
        width: 90%;
        max-width: 42rem;
        border-radius: 1rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: tourModalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        background: white;
        pointer-events: auto;
    }

    .tour-modal.dark {
        background: rgb(17, 24, 39);
    }

    .tour-content {
        padding: 2rem 2.5rem;
        animation: tourStepFadeIn 0.5s ease-out 0.1s both;
    }

    .tour-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .tour-title {
        font-size: 1.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
    }

    .dark .tour-title {
        background: linear-gradient(135deg, #34d399 0%, #6ee7b7 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .tour-description {
        font-size: 0.95rem;
        line-height: 1.6;
        color: rgb(75, 85, 99);
        margin: 0.75rem 0 0 0;
    }

    .dark .tour-description {
        color: rgb(209, 213, 219);
    }

    .tour-close-btn {
        background: none;
        border: none;
        font-size: 1.75rem;
        cursor: pointer;
        color: rgb(107, 114, 128);
        padding: 0;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .tour-close-btn:hover {
        background: rgb(243, 244, 246);
        color: rgb(55, 65, 81);
    }

    .dark .tour-close-btn:hover {
        background: rgb(55, 65, 81);
        color: rgb(229, 231, 235);
    }

    .tour-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.75rem;
        margin: 1.5rem 0;
    }

    @media (max-width: 640px) {
        .tour-body {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
    }

    .tour-visual {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.1) 100%);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 0.75rem;
        padding: 1.5rem;
        min-height: 10rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .tour-visual::before {
        content: ''; 
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
        animation: tourPulse 4s ease-in-out infinite;
    }

    .tour-visual-content {
        position: relative;
        z-index: 1;
        text-align: center;
    }

    .tour-visual-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    .tour-visual-label {
        font-size: 1rem;
        font-weight: 600;
        color: rgb(5, 150, 105);
        margin: 0;
    }

    .dark .tour-visual-label {
        color: rgb(52, 211, 153);
    }

    .tour-items {
        background: rgb(249, 250, 251);
        border-radius: 0.75rem;
        padding: 1.25rem;
        border-left: 4px solid rgb(16, 185, 129);
    }

    .dark .tour-items {
        background: rgb(31, 41, 55);
        border-left-color: rgb(16, 185, 129);
    }

    .tour-items-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .tour-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.9rem;
        color: rgb(55, 65, 81);
    }

    .dark .tour-item {
        color: rgb(209, 213, 219);
    }

    .tour-item-dot {
        display: inline-block;
        width: 0.375rem;
        height: 0.375rem;
        border-radius: 50%;
        background: rgb(16, 185, 129);
        margin-top: 0.375rem;
        flex-shrink: 0;
    }

    .tour-item-text {
        flex: 1;
        line-height: 1.5;
    }

    .tour-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgb(229, 231, 235);
    }

    .dark .tour-footer {
        border-top-color: rgb(55, 65, 81);
    }

    .tour-indicators {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .tour-dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        background: rgb(209, 213, 219);
    }

    .tour-dot:hover {
        transform: scale(1.3);
    }

    .tour-dot.active {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        width: 0.75rem;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    }

    .tour-buttons {
        display: flex;
        gap: 0.75rem;
    }

    .tour-btn {
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s ease;
        user-select: none;
    }

    .tour-btn-skip {
        background: rgb(243, 244, 246);
        color: rgb(75, 85, 99);
    }

    .tour-btn-skip:hover {
        background: rgb(229, 231, 235);
        color: rgb(31, 41, 55);
    }

    .dark .tour-btn-skip {
        background: rgb(55, 65, 81);
        color: rgb(209, 213, 219);
    }

    .dark .tour-btn-skip:hover {
        background: rgb(75, 85, 99);
    }

    .tour-btn-next {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .tour-btn-next:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }

    .tour-btn-next:active {
        transform: translateY(0);
    }

    .tour-tip {
        font-size: 0.8rem;
        color: rgb(107, 114, 128);
        margin-top: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .dark .tour-tip {
        color: rgb(156, 163, 175);
    }

    .tour-tip::before {
        content: '💡';
    }
</style>

<!-- Admin Tour Modal - Vanilla JS with SessionStorage -->
<div id="adminTourWrapper" class="tour-wrapper tour-hidden">
    <div class="tour-backdrop" id="tourBackdrop"></div>
    <div class="tour-modal" id="tourModalBox">
        <div class="tour-content">
            <!-- Header -->
            <div class="tour-header">
                <div>
                    <h2 class="tour-title" id="tourTitle">Ringkasan Dashboard</h2>
                    <p class="tour-description" id="tourDescription">Berisi ringkasan kas dan grafik transaksi.</p>
                </div>
                <button class="tour-close-btn" id="tourCloseBtn" aria-label="Tutup tur">×</button>
            </div>

            <!-- Body: Visual + Items -->
            <div class="tour-body">
                <!-- Visual Illustration -->
                <div class="tour-visual">
                    <div class="tour-visual-content">
                        <div class="tour-visual-icon flex items-center justify-center" id="tourIcon"></div>
                        <p class="tour-visual-label" id="tourVisualLabel">Dashboard</p>
                    </div>
                </div>

                <!-- Items List -->
                <div class="tour-items">
                    <ul class="tour-items-list" id="tourItemsList"></ul>
                    <div class="tour-tip">Anda bisa menekan "Lewati" untuk menutup tur.</div>
                </div>
            </div>

            <!-- Footer: Indicators + Navigation -->
            <div class="tour-footer">
                <div class="tour-indicators" id="tourIndicators"></div>
                <div class="tour-buttons">
                    <button class="tour-btn tour-btn-skip" id="tourSkipBtn">Lewati</button>
                    <button class="tour-btn tour-btn-next" id="tourNextBtn">Berikutnya</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    const tourSteps = [
        {
            title: 'Ringkasan Dashboard',
            description: 'Berisi ringkasan kas, grafik transaksi bulanan, jumlah anggota, dan notifikasi penting untuk operasi Karya Tantri Abadi.',
            icon: '<svg class="w-12 h-12 mx-auto text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2a2 2 0 002-2zm12 0v-3a2 2 0 00-2-2h-2a2 2 0 00-2 2v3a2 2 0 002 2h2a2 2 0 002-2zm0 0v-7a2 2 0 00-2-2h-2a2 2 0 00-2 2v9a2 2 0 002 2h2a2 2 0 002-2z"/></svg>',
            visual: 'Dashboard',
            items: [
                'Ringkasan pemasukan & pengeluaran',
                'Grafik transaksi bulanan untuk analisis cepat',
                'Notifikasi tagihan & jatuh tempo'
            ]
        },
        {
            title: 'Manajemen & Transaksi',
            description: 'Kelola anggota, simpanan, pinjaman, dan pencatatan transaksi harian dengan mudah.',
            icon: '<svg class="w-12 h-12 mx-auto text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>',
            visual: 'Transaksi',
            items: [
                'Tambah / edit / hapus data anggota',
                'Pengajuan & pembayaran pinjaman',
                'Pencatatan simpanan (Pokok, Wajib, Sukarela)'
            ]
        },
        {
            title: 'Inventaris, Laporan & Audit',
            description: 'Pantau stok barang, buat laporan keuangan otomatis (PDF/Excel), dan lihat jejak audit untuk transparansi.',
            icon: '<svg class="w-12 h-12 mx-auto text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
            visual: 'Laporan',
            items: [
                'Peringatan stok rendah & histori perubahan stok',
                'Ekspor laporan: PDF dan Excel',
                'Audit trail: riwayat login & perubahan data'
            ]
        }
    ];

    let currentStep = 0;
    const storageKey = 'admin_tour_shown_v1';

    // Use sessionStorage - clears when browser tab closes
    const storage = sessionStorage;

    // DOM Elements
    const wrapper = document.getElementById('adminTourWrapper');
    const backdrop = document.getElementById('tourBackdrop');
    const titleEl = document.getElementById('tourTitle');
    const descEl = document.getElementById('tourDescription');
    const iconEl = document.getElementById('tourIcon');
    const visualLabelEl = document.getElementById('tourVisualLabel');
    const itemsListEl = document.getElementById('tourItemsList');
    const indicatorsEl = document.getElementById('tourIndicators');
    const nextBtn = document.getElementById('tourNextBtn');
    const skipBtn = document.getElementById('tourSkipBtn');
    const closeBtn = document.getElementById('tourCloseBtn');
    const modalBox = document.getElementById('tourModalBox');

    console.log('%c🎭 Admin Tour System Active', 'font-size: 14px; font-weight: bold; color: #059669;');
    console.log('Commands:');
    console.log('  adminTourShow()   - Show the tour');
    console.log('  adminTourReset()  - Reset & show the tour');

    function updateDarkMode() {
        const isDark = document.documentElement.classList.contains('dark');
        if (isDark) {
            modalBox.classList.add('dark');
        } else {
            modalBox.classList.remove('dark');
        }
    }

    function renderStep() {
        const step = tourSteps[currentStep];
        titleEl.textContent = step.title;
        descEl.textContent = step.description;
        iconEl.innerHTML = step.icon;
        visualLabelEl.textContent = step.visual;

        // Render items
        itemsListEl.innerHTML = '';
        step.items.forEach(item => {
            const li = document.createElement('li');
            li.className = 'tour-item';
            li.innerHTML = `
                <span class="tour-item-dot"></span>
                <span class="tour-item-text">${item}</span>
            `;
            itemsListEl.appendChild(li);
        });

        // Update indicators
        indicatorsEl.innerHTML = '';
        tourSteps.forEach((_, idx) => {
            const dot = document.createElement('button');
            dot.className = `tour-dot ${idx === currentStep ? 'active' : ''}`;
            dot.setAttribute('aria-label', `Langkah ${idx + 1}`);
            dot.addEventListener('click', () => goToStep(idx));
            indicatorsEl.appendChild(dot);
        });

        // Update next button text
        nextBtn.textContent = currentStep < tourSteps.length - 1 ? 'Berikutnya' : 'Selesai';
    }

    function goToStep(idx) {
        if (idx >= 0 && idx < tourSteps.length) {
            currentStep = idx;
            renderStep();
        }
    }

    function nextStep() {
        if (currentStep < tourSteps.length - 1) {
            currentStep++;
            renderStep();
        } else {
            closeTour();
        }
    }

    function closeTour() {
        wrapper.classList.add('tour-hidden');
        wrapper.classList.remove('tour-visible');
        try {
            storage.setItem(storageKey, '1');
            console.log('❌ Tour closed - sessionStorage flag set');
        } catch (e) {
            console.warn('sessionStorage not available', e);
        }
    }

    function showTour() {
        updateDarkMode();
        wrapper.classList.remove('tour-hidden');
        wrapper.classList.add('tour-visible');
        currentStep = 0;
        renderStep();
        console.log('✅ Tour shown');
    }

    function shouldShowTour() {
        try {
            const shown = storage.getItem(storageKey);
            if (shown === '1') {
                console.log('⏭️ Tour already shown this session, skipping');
                return false;
            }
        } catch (e) {
            console.warn('sessionStorage not available', e);
        }

        // Check for force show via query param first
        const params = new URLSearchParams(window.location.search);
        if (params.get('show_tour') === '1') {
            console.log('🔓 Force show via query param');
            return true;
        }

        // Check if we're on any admin/petugas/kasir/spv dashboard
        const path = window.location.pathname || '';
        const isDashboard = (
            (path.includes('/admin') || 
             path.includes('/petugas') || 
             path.includes('/kasir') || 
             path.includes('/spv')) && 
            !path.includes('/login') &&
            path !== '/'
        );

        if (isDashboard) {
            console.log('🏠 On dashboard, should show tour');
        }

        return isDashboard;
    }

    // Event Listeners
    nextBtn.addEventListener('click', nextStep);
    skipBtn.addEventListener('click', closeTour);
    closeBtn.addEventListener('click', closeTour);
    backdrop.addEventListener('click', closeTour);

    // Prevent modal close when clicking modal itself
    modalBox.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Handle dark mode changes
    const observer = new MutationObserver(() => updateDarkMode());
    observer.observe(document.documentElement, { attributes: true });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (wrapper.classList.contains('tour-hidden')) return;
        if (e.key === 'Escape') closeTour();
        if (e.key === 'ArrowRight') nextStep();
        if (e.key === 'ArrowLeft' && currentStep > 0) goToStep(currentStep - 1);
    });

    // Initialize
    function initTour() {
        console.log('🎯 Admin Tour initializing...');
        console.log('   Current path:', window.location.pathname);
        console.log('   sessionStorage flag:', storage.getItem(storageKey));
        console.log('   Should show:', shouldShowTour());
        // If we're on a login/root page, clear the sessionStorage flag so tour can show after re-login
        const path = window.location.pathname || '';
        if (path === '/' || path.includes('/login')) {
            try {
                storage.removeItem(storageKey);
                console.log('🔁 Cleared tour flag because we are on a login/root page');
            } catch (e) {
                console.warn('Could not clear storage on login page', e);
            }
        }

        if (shouldShowTour()) {
            console.log('   → Scheduling tour display in 500ms...');
            setTimeout(() => showTour(), 500);
        }
    }

    // Initialize immediately when script loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTour);
    } else {
        initTour();
    }

    // Also try on window load
    window.addEventListener('load', () => {
        if (wrapper && wrapper.classList.contains('tour-hidden') && shouldShowTour()) {
            console.log('   → Showing tour on load event');
            setTimeout(() => showTour(), 300);
        }
    });

    // Allow manual triggering via window object
    window.adminTourShow = showTour;
    window.adminTourReset = () => {
        try {
            storage.removeItem(storageKey);
            console.log('🔄 Tour sessionStorage cleared');
            showTour();
        } catch (e) {
            console.warn('Error resetting tour', e);
        }
    };

    // Listen for page visibility changes (helps detect logout/redirect)
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && wrapper.classList.contains('tour-hidden')) {
            console.log('📄 Page became visible, checking if tour should show');
            if (shouldShowTour()) {
                setTimeout(() => showTour(), 300);
            }
        }
    });

    console.log('%c🎭 Admin Tour System Active', 'font-size: 14px; font-weight: bold; color: #059669;');
    console.log('Commands:');
    console.log('  adminTourShow()   - Show the tour');
    console.log('  adminTourReset()  - Reset & show the tour');
})();
</script>
