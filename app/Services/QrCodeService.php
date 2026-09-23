<?php

namespace App\Services;

use App\Models\Personnel;
use App\Models\Slot;
use App\Models\Tool;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    // -------------------------------------------------------------------------
    // Model-spesifik QR üreticileri  (data URI döner — doğrudan <img src> olarak kullanılır)
    // -------------------------------------------------------------------------

    /**
     * Parça için SVG QR data URI üret
     * İçerik: sadece seri numarası (TKM-2026-00001)
     * → Tarayıcı okuyunca doğrudan seri no gelir, hızlı zimmet için ideal
     */
    public function forTool(Tool $tool): string
    {
        // Seri no kesinlikle var (boot'ta otomatik atanıyor)
        $content = $tool->serial_no ?? ('TOOL-' . $tool->id);
        return $this->toDataUri($content);
    }

    /**
     * Personel için SVG QR data URI üret
     * İçerik: "PERS:{id}|{badge_number}|{name}"
     */
    public function forPersonnel(Personnel $personnel): string
    {
        $content = 'PERS:' . $personnel->id . '|' . $personnel->badge_number . '|' . $personnel->name;
        return $this->toDataUri($content);
    }

    /**
     * Raf Gözü için SVG QR data URI üret
     * İçerik: "SLOT:{id}|{full_label}"
     */
    public function forSlot(Slot $slot): string
    {
        $content = 'SLOT:' . $slot->id . '|' . $slot->full_label;
        return $this->toDataUri($content);
    }

    // -------------------------------------------------------------------------
    // Alt Seviye Yardımcılar
    // -------------------------------------------------------------------------

    /**
     * Ham metin → SVG string (QR içeriği oluşturur)
     */
    public function generateSvg(string $content, int $size = 200): string
    {
        return QrCode::format('svg')
            ->size($size)
            ->errorCorrection('M')
            ->margin(1)
            ->generate($content);
    }

    /**
     * Ham metin → SVG data URI
     * imagick gerektirmez — SVG tüm sistemlerde çalışır
     */
    public function toDataUri(string $content, int $size = 200): string
    {
        $svg = $this->generateSvg($content, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Geriye dönük uyum: generateSvgDataUri → toDataUri
     */
    public function generateSvgDataUri(string $content, int $size = 200): string
    {
        return $this->toDataUri($content, $size);
    }
}
