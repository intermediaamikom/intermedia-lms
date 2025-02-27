<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use PDF;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Event;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Carbon;

class CertificateController extends Controller
{

    public function generateCertificateNumber(Event $event, User $user)
{
    // Hitung jumlah peserta yang sudah memiliki nomor sertifikat untuk event ini
    $count = $event->users()->whereNotNull('certificate_number')->count();

    // Format nomor sertifikat (001, 002, dst.)
    $certificateNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

    // Ambil kategori peserta dari pivot (default: F)
    $category = $event->users()->where('user_id', $user->id)->first()->pivot->category;

    // Konversi bulan ke Romawi
    $monthRoman = convertToRoman(Carbon::parse($event->occasion_date)->month);

    // Format lengkap nomor sertifikat
    return sprintf(
        '%s/%s/SRT-%s/INTERMEDIA/%s/%s',
        $certificateNumber,
        $category,
        str_replace(' ', '-', strtoupper($event->name)),
        $monthRoman,
        Carbon::parse($event->occasion_date)->year
    );
}

    public function downloadCertificate($id)
    {
        $event = Event::find($id);
        $user = auth()->user();
        $pdf = PDF::loadView('certificate', compact('event', 'user'));
        return $pdf->stream('Kehadiran.pdf');
    }

}
