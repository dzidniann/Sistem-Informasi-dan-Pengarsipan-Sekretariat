<?php

namespace App\Console\Commands;

use App\Models\LegalDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshLegalDocumentStatuses extends Command
{
    protected $signature = 'legal:refresh-document-statuses';

    protected $description = 'Perbarui status dokumen Hukum berdasarkan tanggal review dan kedaluwarsa';

    public function handle(): int
    {
        $changed = 0;
        LegalDocument::whereIn(
            'status',
            array_merge(LegalDocument::DATE_MANAGED_STATUSES, [
                LegalDocument::STATUS_DRAFT,
                LegalDocument::STATUS_NEED_REVIEW,
            ]),
        )
            ->select('id')
            ->chunkById(100, function ($documents) use (&$changed) {
                foreach ($documents as $candidate) {
                    $changed += DB::transaction(function () use ($candidate) {
                        $document = LegalDocument::whereKey($candidate->id)->lockForUpdate()->first();
                        if (!$document || $document->status === $document->statusBasedOnExpiry()) {
                            return 0;
                        }
                        // Model save retains Spatie's change log; no synthetic user is assigned.
                        $document->save();

                        return 1;
                    });
                }
            });

        $this->info("Status {$changed} dokumen berhasil diperbarui.");

        return self::SUCCESS;
    }
}
