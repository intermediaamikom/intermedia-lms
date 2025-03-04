<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Event;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Carbon;

class CertificateController extends Controller
{

    public function generateCertificateNumber(Event $event)
    {
        // Hitung jumlah peserta yang sudah terdaftar di event ini
        $registeredUsersCount = $event->event_user()->count();

        // Hitung sisa kuota
        $remainingQuota = $event->quota - $registeredUsersCount;

        // Pastikan sisa kuota tidak negatif
        if ($remainingQuota < 0) {
            $remainingQuota = 0;
        }

        // Format nomor sertifikat berdasarkan sisa kuota
        $certificateNumber = str_pad($remainingQuota, 3, '0', STR_PAD_LEFT);

        $userCategory = 'F'; // Default F

        // Konversi bulan ke Romawi
        $monthRoman = $this->convertToRoman(Carbon::parse($event->occasion_date)->month);

        return sprintf(
            '%s/%s/SRT-%s/INTERMEDIA/%s/%s',
            $certificateNumber,
            $userCategory,
            str_replace(' ', '-', strtoupper($event->name)),
            $monthRoman,
            Carbon::parse($event->occasion_date)->year
        );
    }

    private function convertToRoman($month)
    {
        $romans = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $romans[$month] ?? '';
    }

    public function downloadCertificate($eventId)
    {
        $event = Event::findOrFail($eventId);
        $user = auth()->user();

        // Cek apakah user sudah terdaftar di event ini
        $pivotData = $event->users()->where('user_id', $user->id)->first();

        if (!$pivotData) {
            return response()->json(['error' => 'Anda belum terdaftar di event ini.'], 404);
        }

        // Jika nomor sertifikat belum ada, generate dan simpan
        if (is_null($pivotData->pivot->number_certificate)) {
            $certificateNumber = $this->generateCertificateNumber($event);

            // Update nomor sertifikat di tabel pivot event_user
            $event->event_user()->updateExistingPivot($user->id, [
                'number_certificate' => $certificateNumber,
            ]);
        } else {
            $certificateNumber = $pivotData->pivot->number_certificate;
        }

        // Generate PDF
        $pdf = Pdf::loadView('certificate', [
            'event' => $event,
            'user' => $user,
            'certificateNumber' => $certificateNumber,
        ]);

        return $pdf->stream('certificate.pdf');
    }
}
