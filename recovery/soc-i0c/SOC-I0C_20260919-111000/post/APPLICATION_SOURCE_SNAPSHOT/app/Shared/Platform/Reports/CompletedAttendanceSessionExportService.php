<?php

namespace App\Shared\Platform\Reports;

use Illuminate\Support\Collection;

class CompletedAttendanceSessionExportService
{
    public function csv(Collection $sessions): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Daftar Sesi Kehadiran Disahkan', 'SISTEM IMTAQ']);
        fputcsv($handle, ['Kelas', 'Pelajaran', 'Tanggal', 'Waktu', 'Hadir', 'Sakit', 'Izin', 'Tidak hadir', 'Belum diisi']);
        foreach ($sessions as $session) {
            $summary = $this->summary($session);
            fputcsv($handle, [
                $session->academicClass?->display_name ?? 'Kelas',
                $session->teachingAssignment?->subject?->subject_name ?? 'Pelajaran',
                $session->planned_start_at->format('d-m-Y'),
                $session->planned_start_at->format('H:i').'–'.$session->planned_end_at->format('H:i'),
                $summary['PRESENT'], $summary['SICK'], $summary['IZIN'], $summary['ABSENT'], $summary['PENDING'],
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    public function pdf(Collection $sessions): string
    {
        $content = "q\n0.07 0.42 0.30 rg\n0 500 842 95 re f\nQ\n";
        $content .= $this->text('DAFTAR SESI KEHADIRAN DISAHKAN', 42, 555, 18, '1 1 1');
        $content .= $this->text('SISTEM IMTAQ  |  Pemeriksaan Waka Akademik', 42, 532, 10, '0.86 0.95 0.90');
        $x = 42;
        $top = 470;
        $height = 28;
        $widths = [118, 170, 85, 70, 52, 52, 52, 65, 65];
        $headers = ['Kelas', 'Pelajaran', 'Tanggal', 'Waktu', 'Hadir', 'Sakit', 'Izin', 'Tidak hadir', 'Belum diisi'];
        $content .= $this->rect($x, $top, array_sum($widths), $height, '0.07 0.42 0.30');
        $cursor = $x;
        foreach ($headers as $i => $header) {
            $content .= $this->text($header, $cursor + 5, $top + 10, 8, '1 1 1');
            $cursor += $widths[$i];
        }
        foreach ($sessions as $index => $session) {
            $y = $top - (($index + 1) * $height);
            if ($y < 70) {
                break;
            }
            $fill = $index % 2 === 0 ? '0.94 0.98 0.96' : '1 1 1';
            $content .= $this->rect($x, $y, array_sum($widths), $height, $fill);
            $summary = $this->summary($session);
            $values = [
                $session->academicClass?->display_name ?? 'Kelas',
                $session->teachingAssignment?->subject?->subject_name ?? 'Pelajaran',
                $session->planned_start_at->format('d-m-Y'),
                $session->planned_start_at->format('H:i'),
                (string) $summary['PRESENT'], (string) $summary['SICK'], (string) $summary['IZIN'],
                (string) $summary['ABSENT'], (string) $summary['PENDING'],
            ];
            $cursor = $x;
            foreach ($values as $i => $value) {
                $content .= $this->text($value, $cursor + 5, $y + 10, 8, '0.10 0.19 0.16');
                $cursor += $widths[$i];
            }
        }
        $content .= $this->text('Pemeriksa: Waka Akademik', 610, 90, 8, '0.35 0.44 0.40');
        $content .= $this->text('____________________', 610, 68, 8, '0.10 0.19 0.16');
        $content .= $this->text('Catatan: hanya sesi akademik resmi berstatus selesai yang ditampilkan.', 42, 48, 8, '0.35 0.44 0.40');
        $content .= $this->text('Dicetak: '.now()->format('d-m-Y H:i').' WIB  |  Halaman 1 dari 1', 42, 30, 8, '0.45 0.52 0.48');

        return $this->document($content);
    }

    public function detailCsv(object $session, Collection $participants): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Detail Kehadiran Santri', 'SISTEM IMTAQ']);
        fputcsv($handle, ['Kelas', $session->academicClass?->display_name ?? 'Kelas', 'Pelajaran', $session->teachingAssignment?->subject?->subject_name ?? 'Pelajaran']);
        fputcsv($handle, ['Tanggal', $session->planned_start_at->format('d-m-Y'), 'Waktu', $session->planned_start_at->format('H:i').'–'.$session->planned_end_at->format('H:i')]);
        fputcsv($handle, ['Santri', 'Kode santri', 'Status', 'Catatan', 'Status pemeriksaan']);
        foreach ($participants as $participant) {
            fputcsv($handle, [$participant->student?->full_name ?? 'Santri', $participant->student?->student_code ?? '', $this->status($participant), $participant->attendance?->notes ?? '', $participant->attendance?->workflow_status === 'VALIDATED' ? 'Sudah diperiksa' : 'Belum diperiksa']);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    public function detailPdf(object $session, Collection $participants): string
    {
        $content = "q\n0.07 0.42 0.30 rg\n0 500 842 95 re f\nQ\n";
        $content .= $this->text('DETAIL KEHADIRAN SANTRI', 42, 555, 18, '1 1 1');
        $content .= $this->text(($session->academicClass?->display_name ?? 'Kelas').'  |  '.($session->teachingAssignment?->subject?->subject_name ?? 'Pelajaran'), 42, 532, 10, '0.86 0.95 0.90');
        $content .= $this->text($session->planned_start_at->format('d-m-Y H:i').'–'.$session->planned_end_at->format('H:i').'  |  Status: Sudah disahkan', 42, 515, 9, '0.86 0.95 0.90');
        $x = 42;
        $top = 475;
        $height = 26;
        $widths = [235, 115, 100, 270, 80];
        $headers = ['Santri', 'Kode santri', 'Status', 'Catatan', 'Periksa'];
        $content .= $this->rect($x, $top, array_sum($widths), $height, '0.07 0.42 0.30');
        $cursor = $x;
        foreach ($headers as $i => $header) {
            $content .= $this->text($header, $cursor + 5, $top + 9, 8, '1 1 1');
            $cursor += $widths[$i];
        }
        foreach ($participants as $index => $participant) {
            $y = $top - (($index + 1) * $height);
            if ($y < 70) {
                break;
            }
            $content .= $this->rect($x, $y, array_sum($widths), $height, $index % 2 === 0 ? '0.94 0.98 0.96' : '1 1 1');
            $values = [$participant->student?->full_name ?? 'Santri', $participant->student?->student_code ?? '', $this->status($participant), $participant->attendance?->notes ?? '—', $participant->attendance?->workflow_status === 'VALIDATED' ? 'Sudah' : 'Belum'];
            $cursor = $x;
            foreach ($values as $i => $value) {
                $content .= $this->text($value, $cursor + 5, $y + 9, 8, '0.10 0.19 0.16');
                $cursor += $widths[$i];
            }
        }
        $content .= $this->text('Pemeriksa: Waka Akademik   ____________________', 530, 55, 8, '0.35 0.44 0.40');
        $content .= $this->text('Dicetak: '.now()->format('d-m-Y H:i').' WIB  |  Halaman 1 dari 1', 42, 30, 8, '0.45 0.52 0.48');

        return $this->document($content);
    }

    private function summary(object $session): array
    {
        $counts = $session->studentParticipants->map(fn ($participant) => $participant->attendance?->attendance_status ?? 'PENDING')->countBy();

        return ['PRESENT' => $counts->get('PRESENT', 0), 'SICK' => $counts->get('SICK', 0), 'IZIN' => $counts->get('IZIN', 0), 'ABSENT' => $counts->get('ABSENT', 0), 'PENDING' => $counts->get('PENDING', 0)];
    }

    private function status(object $participant): string
    {
        return ['PRESENT' => 'Hadir', 'ABSENT' => 'Tidak hadir', 'SICK' => 'Sakit', 'IZIN' => 'Izin', 'LATE' => 'Terlambat', 'EXCUSED' => 'Dikecualikan'][$participant->attendance?->attendance_status ?? ''] ?? 'Belum diisi';
    }

    private function rect(float $x, float $y, float $width, float $height, string $color): string
    {
        return "q\n$color rg\n$x $y $width $height re f\nQ\n";
    }

    private function text(string $value, float $x, float $y, int $size, string $color): string
    {
        return "BT\n/F1 $size Tf\n$color rg\n$x $y Td\n(".$this->escape($value).") Tj\nET\n";
    }

    private function document(string $content): string
    {
        $objects = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>', '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>', '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>', '<< /Length '.strlen($content)." >>\nstream\n$content\nendstream"];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($number + 1)." 0 obj\n$object\nendobj\n";
        } $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], iconv('UTF-8', 'Windows-1252//TRANSLIT', $value));
    }
}
