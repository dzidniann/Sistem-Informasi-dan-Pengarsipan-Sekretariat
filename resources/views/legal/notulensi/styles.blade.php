<style>
    .legal-minutes {
        display: flex;
        flex-direction: column;
        gap: 24px;
        min-width: 0;
    }

    .legal-minutes .page-title {
        font-size: 28px;
        font-weight: 700;
        letter-spacing: normal;
    }

    .legal-minutes .page-subtitle {
        color: #475569;
        line-height: 1.6;
    }

    .legal-minutes .card {
        border: 1px solid #9ed8d0;
        border-radius: 26px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .05);
        padding: 26px;
        min-width: 0;
        margin: 0;
    }

    .legal-minutes .form-label {
        display: block;
        margin: 14px 0 10px;
        color: #334155;
        font-size: .95rem;
        font-weight: 600;
    }

    .legal-minutes .form-control {
        width: 100%;
        border: 1px solid #8bcfc5;
        border-radius: 18px;
        padding: 14px 15px;
        background: #fbfffe;
        color: #0f172a;
    }

    .legal-minutes .form-control:focus {
        border-color: #0f766e;
        box-shadow: 0 0 0 4px rgba(15, 118, 110, .14);
        outline: none;
    }

    .legal-minutes .button,
    .minutes-preview .button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: none;
        border-radius: 999px;
        padding: 13px 20px;
        cursor: pointer;
        font-weight: 700;
        letter-spacing: .01em;
        box-shadow: 0 14px 28px rgba(15, 23, 42, .08);
        text-decoration: none;
    }

    .legal-minutes .button-primary,
    .minutes-preview .button-primary {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: #fff;
    }

    .legal-minutes .button-secondary {
        background: #e6f7f4;
        color: #115e59;
        border: 1px solid #9ed8d0;
    }

    .legal-minutes .button-danger {
        background: #dc2626;
        color: #fff;
    }

    .legal-minutes .topbar {
        border-bottom: 2px solid #8bcfc5;
        padding-bottom: 18px;
    }

    .legal-minutes .search-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: end;
        margin: 14px 0 0;
        width: 100%;
    }

    .legal-minutes .search-group .form-control {
        flex: 1 1 220px;
        min-width: 0;
    }

    .legal-minutes .table-card {
        border: 1px solid #b8d6f5;
        border-radius: 22px;
        padding: 24px;
        margin-top: 20px;
    }

    .legal-minutes .table {
        min-width: 0;
        table-layout: fixed;
    }

    .legal-minutes .table th,
    .legal-minutes .table td {
        padding: 16px 12px;
        border-bottom: 1px solid #d5e2f0;
        font-size: .95rem;
        letter-spacing: normal;
        text-transform: none;
        overflow-wrap: anywhere;
    }

    .legal-minutes .table th {
        color: #0f172a;
    }

    .legal-minutes .table td {
        color: #475569;
    }

    .legal-minutes .table .button {
        padding: 8px 12px;
        font-weight: 500;
    }

    .minutes-preview {
        border: 0;
        border-radius: 14px;
        padding: 0;
        width: 95%;
        max-width: 1000px;
        height: 90vh;
        max-height: 90vh;
        box-shadow: 0 25px 60px rgba(0, 0, 0, .25);
    }

    .minutes-preview::backdrop {
        background: rgba(15, 23, 42, .65);
    }

    .minutes-preview[open] {
        display: flex;
        flex-direction: column;
    }

    .minutes-preview header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .minutes-preview .preview-content {
        flex: 1;
        min-height: 0;
        overflow: auto;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    @media(max-width:640px) {
        .legal-minutes .card {
            padding: 18px;
        }

        .legal-minutes .table-card {
            padding: 8px;
        }

        .legal-minutes .table th,
        .legal-minutes .table td {
            padding: 12px 6px;
        }
    }
</style>
