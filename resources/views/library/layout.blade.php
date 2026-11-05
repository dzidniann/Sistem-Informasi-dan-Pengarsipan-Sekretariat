@extends(
    match (auth()->user()->role?->nama) {
        'legal-admin' => 'legal.layout',
        'sekretariat' => 'sekretariat.layout',
        'humas' => 'humas.layout',
        'staf-bidang' => 'staf.layout',
        'super-admin' => 'admin.layout',
        default => 'employee.layout'
    }
)

@section('content')
    <style>
        /* Keep library styles inside the content area so each role's sidebar stays intact. */
        .library-page {
            min-width: 0;
        }

        .library-page .card-header {
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #d5e2f0;
        }

        .library-page .card-title {
            margin: 0;
            color: #0f172a;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .library-page .card-meta {
            margin: 6px 0 0;
            color: #64748b;
            font-size: .95rem;
            line-height: 1.6;
        }

        .library-page .card.library-search-banner {
            background: linear-gradient(135deg, #172033, #244a82);
            border-color: #416a9e;
            color: #fff;
            padding: 32px;
        }

        .library-search-label {
            display: inline-flex;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .15);
            color: #dbeafe;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .library-search-title {
            margin: 14px 0 8px;
            color: #fff;
            font-size: clamp(1.4rem, 2.5vw, 2rem);
            font-weight: 700;
            line-height: 1.3;
        }

        .library-search-description {
            max-width: 760px;
            margin: 0 0 22px;
            color: #dbeafe;
            font-size: .95rem;
            line-height: 1.6;
        }

        .library-search-banner form {
            max-width: 850px;
        }

        .library-page .library-search-banner .btn-primary {
            background: #fff7e6;
            border: 1px solid #f1d58d;
            color: #92400e;
        }

        .library-page .library-search-banner .btn-primary:hover {
            background: #fef0cd;
        }

        .library-page .card {
            background: #fff;
            border: 1px solid #b8d6f5;
            border-radius: 26px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .05);
            padding: 26px;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .library-page .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: 999px;
            padding: 12px 18px;
            cursor: pointer;
            font-weight: 600;
            font-size: .9rem;
            text-decoration: none;
        }

        .library-page .btn-primary {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #fff;
        }

        .library-page .btn-primary:hover {
            background: #1e293b;
        }

        .library-page .btn-light {
            background: #e6f7f4;
            color: #115e59;
            border: 1px solid #9ed8d0;
        }

        .library-page .btn-light:hover {
            background: #d0eee7;
        }

        .library-page .btn-sm {
            padding: 6px 12px;
            font-size: .8rem;
        }

        .library-page .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .library-page .badge-active {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .library-page .badge-cat {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .library-page .form-control {
            width: 100%;
            border: 1px solid #8bcfc5;
            border-radius: 18px;
            padding: 14px 15px;
            background: #fbfffe;
            color: #0f172a;
            font-size: .92rem;
        }

        .library-page .form-control:focus {
            outline: none;
            border-color: #0f766e;
            box-shadow: 0 0 0 4px rgba(15, 118, 110, .14);
        }

        .library-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
            gap: 20px;
        }

        .library-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr);
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 640px) {

            .library-page .card,
            .library-page .card.library-search-banner {
                padding: 18px;
            }
        }

        @media (max-width: 1200px) {
            .library-detail-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
    <div class="library-page">
        @yield('library-content')
    </div>
@endsection
