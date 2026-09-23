<?php

namespace App\Observers;

use App\Models\Loan;

class LoanObserver
{
    /**
     * Yeni zimmet oluşturulduğunda:
     * - Parça durumu "loaned" (Ödünçte) olur
     * - Parçanın slot_id'si NULL yapılır (artık rafta değil)
     */
    public function created(Loan $loan): void
    {
        $loan->tool()->update([
            'status'  => 'loaned',
            'slot_id' => null,
        ]);
    }

    /**
     * Zimmet güncellendiğinde:
     * - Eğer "returned" durumuna geçildiyse: parça "available" ve iade gözüne yerleştirilir
     * - Eğer "overdue" durumuna geçildiyse: parça hâlâ "loaned" kalır (ödünçte ama gecikmeli)
     */
    public function updated(Loan $loan): void
    {
        if ($loan->wasChanged('status')) {

            if ($loan->status === 'returned') {
                $loan->tool()->update([
                    'status'  => 'available',
                    'slot_id' => $loan->returned_slot_id, // Konumu doğrulayarak iade rafına yerleştir
                ]);
            }

            // 'overdue' durumuna geçişte parça hâlâ ödünçte — durum değişmez
        }
    }
}
