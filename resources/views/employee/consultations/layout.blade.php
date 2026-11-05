@extends(
    match (auth()->user()->role?->nama) {
        'sekretariat' => 'sekretariat.layout',
        'humas' => 'humas.layout',
        'staf-bidang' => 'staf.layout',
        'super-admin' => 'admin.layout',
        'legal-admin' => 'legal.layout',
        default => 'employee.layout'
    }
)

@section('content')
    <style>
        .consultation-portal {
            display: flex;
            flex-direction: column;
            gap: 24px;
            min-width: 0;
        }

        .consultation-portal .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
            padding: 24px;
            min-width: 0;
        }

        .consultation-portal .card-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
        }

        .consultation-portal .topbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            padding-bottom: 18px;
            border-bottom: 1.5px solid #e2e8f0;
        }

        .consultation-portal .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 999px;
            padding: 10px 18px;
            cursor: pointer;
            font-weight: 600;
            font-size: .9rem;
            text-decoration: none;
            min-width: 0;
        }

        .consultation-portal .btn-primary {
            background: #0284c7;
            color: #fff;
            box-shadow: 0 4px 14px rgba(2, 132, 199, .25);
        }

        .consultation-portal .btn-primary:hover {
            background: #0369a1;
        }

        .consultation-portal .btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        .consultation-portal .btn-sm {
            padding: 6px 12px;
            font-size: .8rem;
        }

        .consultation-portal .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .consultation-portal .badge-need-review {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .consultation-portal .badge-active {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .consultation-portal .badge-info {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #7dd3fc;
        }

        .consultation-portal .badge-draft {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .consultation-portal .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-bottom: 16px;
        }

        .consultation-portal .form-label {
            color: #334155;
            font-size: .9rem;
            font-weight: 600;
        }

        .consultation-portal .form-control {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 11px 14px;
            background: #fff;
            color: #0f172a;
            font-size: .92rem;
        }

        .consultation-portal .form-control:focus {
            outline: 2px solid #0284c7;
            outline-offset: 2px;
        }

        .consultation-portal textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .consultation-portal .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        .consultation-portal .table th {
            padding: 13px 16px;
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid #e2e8f0;
        }

        .consultation-portal .table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: .92rem;
            color: #334155;
        }
    </style>
    <div class="consultation-portal">
        @php($role = auth()->user()->role?->nama)
        @if (in_array($role, ['sekretariat', 'staf-bidang', 'super-admin'], true))
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif
        @yield('consultation-content')
    </div>
@endsection
