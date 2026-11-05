<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use ZipArchive;

class HumasWordPreview
{
    public function paragraphs(string $path): ?array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }

        try {
            $entry = $zip->statName('word/document.xml');
            if (!$entry || $entry['size'] > 10 * 1024 * 1024) {
                return null;
            }

            $xml = $zip->getFromName('word/document.xml');
            if ($xml === false || stripos($xml, '<!DOCTYPE') !== false) {
                return null;
            }

            $previous = libxml_use_internal_errors(true);
            try {
                $document = new DOMDocument();
                if (!$document->loadXML($xml, LIBXML_NONET)) {
                    return null;
                }

                $xpath = new DOMXPath($document);
                $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                $paragraphs = [];
                foreach ($xpath->query('//w:body//w:p') as $paragraph) {
                    $text = '';
                    foreach ($xpath->query('.//w:t | .//w:tab | .//w:br', $paragraph) as $node) {
                        $text .= match ($node->localName) {
                            'tab' => "\t",
                            'br' => "\n",
                            default => $node->textContent,
                        };
                    }
                    $paragraphs[] = $text;
                }

                return $paragraphs;
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
        } finally {
            $zip->close();
        }
    }
}
