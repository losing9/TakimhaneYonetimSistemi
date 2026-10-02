<style>
/* ─── Endüstriyel Koyu Tema (admin_dashboard.jpg) için Filament v3 Global CSS ─── */
:root {
    --primary-50: 255 251 235;
    --primary-100: 254 243 199;
    --primary-200: 253 230 138;
    --primary-300: 252 211 77;
    --primary-400: 251 191 36;
    --primary-500: 245 158 11;
    --primary-600: 217 119 6;
    --primary-700: 180 83 9;
    --primary-800: 146 64 14;
    --primary-900: 120 53 15;
    --primary-950: 69 26 3;
}

/* Filament Arkaplan & Shell */
html.dark, .dark body {
    background-color: #0e1219 !important;
    color: #f8fafc !important;
}

/* Sol Sidebar */
.dark aside.fi-sidebar {
    background-color: #0b0e14 !important;
    border-right: 1px solid #1f2837 !important;
}

/* Sidebar aktif öğe */
.dark .fi-sidebar-item-active .fi-sidebar-item-button {
    background-color: #f59e0b !important;
    color: #0c1017 !important;
    font-weight: 800 !important;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4) !important;
}
.dark .fi-sidebar-item-active .fi-sidebar-item-button svg {
    color: #0c1017 !important;
}

/* Topbar Header */
.dark .fi-topbar {
    background-color: #141923 !important;
    border-bottom: 1px solid #232d3d !important;
}

/* Filament Kartlar & Bölümler */
.dark .fi-section,
.dark .fi-card,
.dark .fi-ta-ctn,
.dark .fi-wi-stats-overview-stat {
    background-color: #18202c !important;
    border: 1px solid #263346 !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.3) !important;
}

/* Tablolar */
.dark .fi-ta-header,
.dark .fi-ta-table th {
    background-color: #131a24 !important;
    color: #8b9bb4 !important;
    font-weight: 800 !important;
    letter-spacing: 0.6px !important;
    text-transform: uppercase !important;
    font-size: 11px !important;
}

.dark .fi-ta-table tr:hover td {
    background-color: #1d2736 !important;
}

.dark .fi-ta-table td {
    border-bottom: 1px solid rgba(38, 51, 70, 0.6) !important;
}

/* Butonlar */
.dark .fi-btn-primary {
    background-color: #f59e0b !important;
    color: #0c1017 !important;
    font-weight: 800 !important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3) !important;
}
.dark .fi-btn-primary:hover {
    background-color: #fbbf24 !important;
}

/* Inputlar */
.dark .fi-input-wrp {
    background-color: #111620 !important;
    border: 1px solid #263346 !important;
}
.dark .fi-input-wrp:focus-within {
    border-color: #f59e0b !important;
    box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.25) !important;
}
</style>
