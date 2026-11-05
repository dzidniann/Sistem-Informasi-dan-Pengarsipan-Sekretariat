<style>
    /* Shared screen layout. PDF templates do not include this stylesheet. */
    .app-shell,
    .main-content,
    .page-container,
    .main-content :where(div, section, article, form, fieldset) {
        min-width: 0;
    }

    .main-content .page-container {
        width: 100%;
    }

    .main-content {
        overflow-wrap: anywhere;
    }

    .main-content pre {
        white-space: pre-wrap;
    }

    .main-content :where(h1, h2, h3, h4, p, dd, label, .user-badge, .badge) {
        overflow-wrap: anywhere;
    }

    .main-content :where(img, video, canvas, iframe, embed, object) {
        max-width: 100%;
    }

    .main-content :where(input, select, textarea) {
        min-width: 0;
        max-width: 100%;
    }

    .main-content .table-wrapper,
    .main-content .table-card {
        max-width: 100%;
        overflow-x: auto;
        overscroll-behavior-x: contain;
        -webkit-overflow-scrolling: touch;
    }

    .main-content .table-wrapper:focus-visible {
        outline: 2px solid #0f766e;
        outline-offset: 3px;
    }

    .main-content .detail-table {
        min-width: 0;
        table-layout: fixed;
    }

    /* Preserve whole words in tables; only long user-entered values may wrap. */
    .main-content .table th {
        white-space: nowrap;
        overflow-wrap: normal;
        word-break: normal;
    }

    .main-content .table td {
        overflow-wrap: break-word;
        word-break: normal;
        vertical-align: middle;
        line-height: 1.5;
    }

    .main-content .table .cell-primary {
        overflow-wrap: anywhere;
    }

    .record-table .cell-primary > a,
    .record-table .cell-primary > strong {
        display: -webkit-box !important;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        line-clamp: 3;
        overflow: hidden;
    }

    .main-content .table :where(.badge, .btn, .button, .document-version, .document-date) {
        white-space: nowrap;
        overflow-wrap: normal;
        word-break: normal;
        flex-shrink: 0;
        max-width: none;
    }

    .main-content .document-table {
        min-width: 1080px;
        table-layout: auto;
    }

    .main-content .document-table :where(th, td) {
        padding: 16px 12px;
        font-size: .875rem;
    }

    .document-table .cell-primary {
        width: 28%;
        min-width: 220px;
    }

    .document-table .document-category {
        min-width: 120px;
    }

    .document-table .document-people {
        min-width: 120px;
    }

    .document-table .document-dates {
        min-width: 144px;
    }

    .document-table .document-actions {
        min-width: 132px;
    }

    .document-actions > div {
        flex-wrap: nowrap;
    }

    .main-content .legal-filters > div:last-child {
        flex: 0 0 auto;
        min-width: 120px;
    }

    .main-content :is(.legal-filters, .humas-filters) :where(button, .btn, .button) {
        flex-shrink: 0;
        min-height: 44px;
        white-space: nowrap;
        overflow-wrap: normal;
    }

    .main-content .dashboard-table {
        table-layout: auto;
    }

    .dashboard-table .cell-primary {
        width: 42%;
    }

    .consultation-detail-grid {
        grid-template-columns: minmax(0, 1.8fr) minmax(300px, 1.1fr);
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 6px;
    }

    .calendar-grid > * {
        min-width: 0;
    }

    .calendar-navigation,
    .calendar-heading {
        flex-wrap: wrap;
        gap: 8px;
    }

    @media screen and (min-width: 981px) {
        .app-shell > .sidebar {
            position: sticky;
            top: 0;
            align-self: flex-start;
            flex-shrink: 0;
            height: 100vh;
            height: 100dvh;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior-y: contain;
        }

        .app-shell > .sidebar > .sidebar-inner {
            min-height: 100%;
        }
    }

    @media screen and (max-width: 1100px) {
        .main-content .grid-2,
        .main-content .consultation-detail-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media screen and (max-width: 980px) {
        .app-shell {
            width: 100%;
        }

        .app-shell .sidebar {
            width: 280px;
            min-width: 0;
            max-width: calc(100vw - 48px);
            height: 100vh;
            height: 100dvh;
            overflow-y: auto;
            overscroll-behavior-y: contain;
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }

        .app-shell .sidebar-inner {
            min-height: 100%;
        }

        body.sidebar-modal-open {
            overflow: hidden;
        }

        .app-shell .sidebar-close {
            min-width: 44px;
            min-height: 44px;
        }

        .main-content {
            width: 100%;
            padding: 18px;
        }

        .sidebar-toolbar {
            margin-left: 0;
        }

        .main-content .sidebar-toggle {
            flex: 0 0 44px;
            width: 44px;
            min-width: 44px;
            max-width: 44px;
            height: 44px;
            min-height: 44px;
            max-height: 44px;
        }
    }

    @media screen and (max-width: 640px) {
        .main-content {
            padding: 16px 12px;
        }

        .page-container,
        .consultation-portal {
            gap: 18px;
        }

        .main-content .page-title {
            font-size: 1.4rem;
            line-height: 1.3;
        }

        .main-content :where(.card, .table-card, .chart-card, .category-card),
        .main-content .consultation-portal .card {
            padding: 16px;
            border-radius: 18px;
        }

        .main-content .topbar,
        .main-content .page-header,
        .main-content .user-badge {
            max-width: 100%;
        }

        .main-content :where(.stat-grid, .humas-grid, .humas-filters) {
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .main-content :where(input, select, textarea),
        .consultation-portal .form-control {
            min-width: 0 !important;
            font-size: 16px !important;
        }

        .main-content input[type="file"] {
            width: 100%;
            white-space: normal;
        }

        .main-content :where(button, .button, .btn) {
            min-width: 0;
            min-height: 44px;
            max-width: 100%;
            white-space: normal;
            text-align: center;
        }

        .main-content :where(.search-group, .legal-filters, .humas-filters) {
            width: 100%;
        }

        .main-content :where(.search-group, .legal-filters) > * {
            flex: 1 1 100%;
            width: 100%;
            min-width: 0;
        }

        .main-content .legal-filters > div,
        .main-content .legal-filters > div:last-child {
            flex: 1 1 100%;
            width: 100%;
            min-width: 0;
        }

        .main-content :where(div, form)[style*="display:flex"]:not([style*="flex-direction:column"]):not([style*="flex-direction: column"]),
        .main-content :where(div, form)[style*="display: flex"]:not([style*="flex-direction:column"]):not([style*="flex-direction: column"]) {
            flex-wrap: wrap;
        }

        .main-content .table th,
        .main-content .table td {
            padding: 12px;
        }

        .main-content .table-wrapper {
            border-radius: 10px;
        }

        /* Compact summaries and document lists become readable record cards. */
        .main-content .record-table,
        .main-content .record-table tbody {
            display: block;
            width: 100%;
            min-width: 0;
        }

        .main-content .record-table thead {
            display: none;
        }

        .main-content .record-table tbody tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px 16px;
            position: relative;
            padding: 16px 0;
            border-bottom: 1px solid #dbe4ef;
        }

        .main-content .document-table tbody tr {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            padding: 18px 16px;
        }

        .main-content .record-table tbody tr:last-child {
            border-bottom: 0;
        }

        .main-content .record-table td {
            display: block;
            width: auto;
            min-width: 0;
            padding: 0;
            border: 0;
            font-size: .875rem;
            text-align: left !important;
            overflow-wrap: anywhere;
        }

        .record-table td[data-label]::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 4px;
            color: #64748b;
            font-size: .75rem;
            font-weight: 500;
        }

        .main-content .record-table .cell-primary,
        .main-content .record-table .cell-actions,
        .main-content .record-table .cell-empty {
            grid-column: 1 / -1;
        }

        .record-table .cell-primary::before,
        .record-table .cell-actions::before {
            display: none !important;
        }

        .main-content .record-table .cell-primary :where(a, strong) {
            font-size: 1rem !important;
            line-height: 1.5;
        }

        .main-content .document-table .cell-primary {
            padding-right: 32px;
        }

        .main-content .document-table .row-number {
            position: absolute;
            top: 20px;
            right: 16px;
            font-size: .75rem;
        }

        .main-content .record-table .row-number::before {
            display: inline;
            margin-right: 3px;
        }

        .main-content .record-table .cell-actions {
            display: flex;
            justify-content: flex-end;
        }

        .main-content .record-table .cell-empty {
            padding: 12px !important;
        }

        .main-content .detail-table,
        .main-content .detail-table :where(tbody, tr, th, td) {
            display: block;
            width: 100% !important;
        }

        .main-content .detail-table th,
        .main-content .detail-table td {
            border-bottom: 0;
            padding: 6px 0;
        }

        .main-content .detail-table tr {
            padding: 8px 0;
            border-bottom: 1px solid #dbe4ef;
        }

        .calendar-card {
            padding: 12px !important;
        }

        .calendar-grid {
            gap: 3px;
        }

        .calendar-grid.calendar-days > * {
            padding: 3px !important;
            min-height: 68px !important;
        }

        .calendar-grid a {
            max-width: 100%;
            min-height: 30px;
            padding: 4px 2px !important;
        }

        #modal-preview-berkas {
            padding: 8px !important;
        }

        #modal-preview-berkas > div {
            width: 100% !important;
            height: 94vh !important;
            height: 94dvh !important;
            min-height: 0;
        }

        #modal-preview-berkas > div > div:first-child {
            padding: 12px !important;
            gap: 8px;
        }

        #preview-berkas-content {
            min-height: 0;
            padding: 8px !important;
        }

        .modal-box {
            width: calc(100% - 24px);
            max-width: calc(100vw - 24px);
            max-height: 90vh;
            max-height: 90dvh;
            overflow-y: auto;
        }
    }

    @media screen and (max-width: 359px) {
        .main-content .document-table tbody tr {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .sidebar,
        .nav-link {
            transition: none;
        }
    }
</style>
