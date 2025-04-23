<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Event;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use TCPDF;

class CertificateController extends Controller
{
    public function generateCertificateNumber(Event $event, User $user)
    {
        $existingCertificate = $event->event_users()
            ->where('user_id', $user->id)
            ->whereNotNull('number_certificate')
            ->first();

        if ($existingCertificate) {
            return $existingCertificate->pivot->number_certificate;
        }

        $lastCertificateNumber = DB::table('event_users')
            ->whereNotNull('number_certificate')
            ->orderBy('number_certificate', 'desc')
            ->value('number_certificate');

        if (!$lastCertificateNumber) {
            $certificateNumber = '001';
        } else {
            $lastNumber = intval(substr($lastCertificateNumber, 0, 3));
            $nextNumber = $lastNumber + 1;
            $certificateNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
        $userCategory = 'F'; // Default F
        $monthRoman = $this->convertToRoman(Carbon::parse($event->occasion_date)->month);

        return sprintf(
            '%s/%s/SRT-BE SMART/INTERMEDIA/%s/%s',
            $certificateNumber,
            $userCategory,
            $monthRoman,
            Carbon::parse($event->occasion_date)->year
        );
    }

    public function getAttendanceValue($eventId, $userId)
    {
        $attendance = Attendance::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if ($attendance) {
            return [
                'participantValue' => $attendance->participation_score,
                'submissionValue' => $attendance->submission_score,
            ];
        }
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
            12 => 'XII'
        ];
        return $romans[$month] ?? '';
    }

    public function downloadCertificate($eventId)
    {
        $event = Event::findOrFail($eventId);
        $user = auth()->user();

        $pivotData = $event->event_users()->where('user_id', $user->id)->first();

        if (!$pivotData) {
            return response()->json(['error' => 'Anda Belum Terdaftar Di Event Ini.'], 404);
        }

        $certificateNumber = $pivotData->pivot->number_certificate;

        $attendanceValues = $this->getAttendanceValue($event->id, $user->id);

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $horizontalMargin = 25.91;
        $contentWidth = 245.35;

        $pdf->setTitle("SERTIFIKAT " . strtoupper($user->name) . " - " . strtoupper($event->name));
        $pdf->setCreator('Intermedia LMS');
        $pdf->setAuthor('Keilmuan Intermedia');

        $pdf->setMargins(0, 0, 0, false);

        $pdf->setAutoPageBreak(false);

        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();

        $pdf->AddPage();

        $pdf->Image(resource_path('images/be-smart-cert-1.png'), 0, 0, $pageWidth, $pageHeight);

        $pdf->Ln(47.5);
        $pdf->setTextColor(25, 73, 48);
        $pdf->setFillColor(249, 250.0, 252.0);
        $pdf->setFont('', 'B', 13);
        $pdf->Cell($horizontalMargin, 6, '', 0, 0);
        $pdf->Cell($contentWidth, 6, "Nomor: " . strtoupper($certificateNumber), 0, 1, 'C', true);

        $pdf->Ln(20.68);
        $pdf->setFont('', 'B', 20);
        $pdf->Cell($horizontalMargin, 6, '', 0, 0);
        $pdf->Cell($contentWidth, 6, strtoupper($user->name), 0, 1, 'C', true);

        $pdf->Ln(3.87);
        $pdf->setFont('', 'B', 15);
        $pdf->Cell($horizontalMargin, 6, '', 0, 0);
        $pdf->Cell($contentWidth, 6, "DIVISI " . strtoupper($user->division->name), 0, 1, 'C', true);

        $pdf->Ln(16.02);
        $pdf->setFont('', 'B', 15);
        $pdf->Cell($horizontalMargin, 6, '', 0, 0);
        $pdf->Cell($contentWidth, 6, $event->name, 0, 1, 'C', true);

        $pdf->Ln(12.31);
        $pdf->setFont('', '', 12);
        $pdf->setTextColor(0, 0, 0);
        $pdf->Cell($horizontalMargin, 3, '', 0, 0);
        $pdf->Cell($contentWidth, 3, Carbon::parse($event->occasion_date)->isoFormat('dddd, D MMMM YYYY'), 0, 1, 'C', true);

        $pdf->Image(resource_path('images/signature-febri.png'), 213.11, 155.48, 25.23, 25.23);

        $pdf->AddPage();

        $startScoreBoxY = 93.64;
        $startScoreBoxX = 148.76;

        $scoreBoxWidth = 68.16;
        $scoreBoxHeight = 21.25;

        $pdf->Image(resource_path('images/be-smart-cert-2.png'), 0, 0, $pageWidth, $pageHeight);

        $startYNoSertif = 48.01;
        $noSertifHeight = 6;

        $pdf->Ln($startYNoSertif);
        $pdf->setTextColor(25, 73, 48);
        $pdf->setFillColor(249, 250.0, 252.0);
        $pdf->setFont('', 'B', 13);
        $pdf->Cell($horizontalMargin, $noSertifHeight, '', 0, 0);
        $pdf->Cell($contentWidth, $noSertifHeight, "Nomor: " . strtoupper($certificateNumber), 0, 1, 'C', true);

        $pdf->Ln($startScoreBoxY - ($startYNoSertif + $noSertifHeight));
        $pdf->setFont('', 'B', 15);
        $pdf->Cell($startScoreBoxX, $scoreBoxHeight, '', 0, 0);
        $pdf->Cell($scoreBoxWidth, $scoreBoxHeight, $attendanceValues['participantValue'], 0, 1, 'C', true);

        $pdf->Ln(0.5); // Box Border Thickness
        $pdf->setFont('', 'B', 15);
        $pdf->Cell($startScoreBoxX, $scoreBoxHeight, '', 0, 0);
        $pdf->Cell($scoreBoxWidth, $scoreBoxHeight, $attendanceValues['submissionValue'], 0, 1, 'C', true);

        $pdf->Image(resource_path('images/signature-febri.png'), 140.21, 160.78, 25.23, 25.23);

        return response($pdf->Output('multi-page-background.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . str_replace(' ', '-', strtoupper($user->name)) . '-' . str_replace(' ', '-', strtoupper($user->name)) . '.pdf"');
    }
}
