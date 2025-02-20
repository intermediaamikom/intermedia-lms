<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Event;
use App\Models\User;

class CertificateController extends Controller
{
    public function downloadCertificate($id)
    {
        $event = Event::find($id);
        $user = auth()->user();
        $pdf = PDF::loadView('certificate', compact('event', 'user'));
        return $pdf->stream('Kehadiran.pdf');
    }

}
