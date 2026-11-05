<style>
    .main-content {
        min-width: 0;
        padding: 30px 26px;
    }

    .sidebar-toolbar {
        display: flex;
        justify-content: flex-start;
        margin: 0 0 20px -14px;
    }

    .sidebar-toolbar .sidebar-toggle {
        display: flex;
        position: static;
        flex: 0 0 42px;
        padding: 0;
        background: #0f172a;
        color: #fff;
        border: none;
        border-radius: 12px;
        width: 42px;
        min-width: 42px;
        max-width: 42px;
        height: 42px;
        min-height: 42px;
        max-height: 42px;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(15, 23, 42, .2);
    }

    .sidebar-close {
        display: none;
    }

    .sidebar-toggle:focus-visible,
    .sidebar-close:focus-visible {
        outline: 3px solid #0f766e;
        outline-offset: 3px;
    }

    @media (max-width: 980px) {
        .main-content {
            padding: 18px;
        }

        .sidebar-toolbar {
            margin-left: 0;
        }

        .sidebar {
            width: 260px;
            min-width: 0;
            max-width: calc(100vw - 56px);
            overflow-y: auto;
        }

        .sidebar-close {
            display: inline-flex;
            min-width: 0;
            align-items: center;
            justify-content: center;
            gap: 8px;
            align-self: flex-end;
            border: 1px solid #334155;
            border-radius: 10px;
            background: #1e293b;
            color: #fff;
            padding: 6px 12px;
            cursor: pointer;
        }
    }
</style>
