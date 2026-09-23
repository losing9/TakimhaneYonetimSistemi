<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DailyReport extends Model
{
    protected $fillable = [
        'report_date',
        'total_loans',
        'total_returns',
        'overdue_count',
        'new_tools',
        'file_path_xlsx',
        'file_path_pdf',
        'generated_at',
    ];

    protected $casts = [
        'report_date'  => 'date',
        'generated_at' => 'datetime',
    ];

    /**
     * Excel dosyasının var olup olmadığı
     */
    public function hasXlsx(): bool
    {
        return $this->file_path_xlsx && Storage::exists($this->file_path_xlsx);
    }

    /**
     * PDF dosyasının var olup olmadığı
     */
    public function hasPdf(): bool
    {
        return $this->file_path_pdf && Storage::exists($this->file_path_pdf);
    }

    /**
     * Tarih Türkçe format
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->report_date->format('d.m.Y');
    }
}
