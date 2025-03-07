<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Event;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
            '%s/%s/SRT-%s/INTERMEDIA/%s/%s',
            $certificateNumber,
            $userCategory,
            str_replace(' ', '-', strtoupper($event->name)),
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

        // Generate PDF
        $pdf = Pdf::loadView('certificate', [
            'event' => $event,
            'user' => $user,
            'certificateNumber' => $certificateNumber,
            'participantValue' => $attendanceValues['participantValue'],
            'submissionValue' => $attendanceValues['submissionValue'],
        ]);

        return $pdf->stream('certificate.pdf');
    }
}
