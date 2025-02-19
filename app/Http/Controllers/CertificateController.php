<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Models\Event;
use App\Models\User;

class CertificateController extends Controller
{
    public function downloadCertificate($id)
    {
        $event = Event::find($id);
        $user = auth()->user(); // Atau dapatkan user sesuai kebutuhan Anda
        $pdf = PDF::loadView('certificate', compact('event', 'user'));
        return $pdf->download('Kehadiran.pdf');
    }
}
