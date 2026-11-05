<?php

return [
    'qpdf_binary' => env(
        'UPLOAD_QPDF_BINARY',
        PHP_OS_FAMILY === 'Windows' && env('APP_ENV') !== 'testing' && is_file(base_path('tools/qpdf/bin/qpdf.exe'))
            ? base_path('tools/qpdf/bin/qpdf.exe')
            : 'qpdf',
    ),
    'require_pdf_optimizer' => env('UPLOAD_REQUIRE_PDF_OPTIMIZER', env('APP_ENV') !== 'testing'),
];
