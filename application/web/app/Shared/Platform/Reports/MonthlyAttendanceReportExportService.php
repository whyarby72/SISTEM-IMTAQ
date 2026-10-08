<?php

namespace App\Shared\Platform\Reports;

use App\Domains\Academic\Models\MonthlyAttendanceSummary;
use Illuminate\Support\Collection;
use Mpdf\Mpdf;

class MonthlyAttendanceReportExportService
{
    public function __construct(private readonly MonthlyAttendanceReportContext $context) {}

    public function rows(): Collection
    {
        return MonthlyAttendanceSummary::query()
            ->with(['academicClass', 'importBatch'])
            ->where('period', '2026-07')
            ->get()
            ->sortBy(function (MonthlyAttendanceSummary $row): int {
                $name = preg_replace('/\s*\(Pilot\)$/', '', $row->academicClass?->display_name ?? '');

                return ['Kelas 1' => 1, 'Kelas 2A' => 2, 'Kelas 2B' => 3, 'Kelas 3A' => 4, 'Kelas 3B' => 5][$name] ?? 99;
            })
            ->values();
    }

    public function csv(): string
    {
        $rows = $this->rows();
        $context = $this->context->resolve($rows, collect());
        $handle = fopen('php://temp', 'r+');
        $escape = '\\';
        fputcsv($handle, ['Arsip Kehadiran Historis', $context['period_label'], 'Tahun Ajaran 2026/2027'], ',', '"', $escape);
        fputcsv($handle, ['Jenis laporan', 'Historical attendance snapshot'], ',', '"', $escape);
        fputcsv($handle, ['Source type', $context['source_type']], ',', '"', $escape);
        fputcsv($handle, ['Source system', $context['source_system']], ',', '"', $escape);
        fputcsv($handle, ['Live data', 'NO'], ',', '"', $escape);
        fputcsv($handle, ['Publication status', $context['publication_status']], ',', '"', $escape);
        fputcsv($handle, ['Source reference', $context['source_reference_label']], ',', '"', $escape);
        fputcsv($handle, [$context['historical_notice']], ',', '"', $escape);
        fputcsv($handle, ['Kelas', 'Hadir', 'Izin', 'Sakit', 'Absen', 'Kesempatan hadir dihitung', 'Dikecualikan dari denominator', '% Kehadiran'], ',', '"', $escape);
        foreach ($rows as $row) {
            fputcsv($handle, [preg_replace('/\s*\(Pilot\)$/', '', $row->academicClass?->display_name ?? $row->class_id), $row->present, $row->permission, $row->sick, $row->absent, $row->eligible, $row->non_eligible, number_format((float) $row->attendance_rate * 100, 2, ',', '.').'%'], ',', '"', $escape);
        }
        fputcsv($handle, ['TOTAL', $rows->sum('present'), $rows->sum('permission'), $rows->sum('sick'), $rows->sum('absent'), $rows->sum('eligible'), $rows->sum('non_eligible'), number_format($rows->sum('eligible') > 0 ? ($rows->sum('present') / $rows->sum('eligible')) * 100 : 0, 2, ',', '.').'%'], ',', '"', $escape);
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    public function pdf(): string
    {
        $rows = $this->rows();
        $pdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L', 'margin_left' => 14, 'margin_right' => 14, 'margin_top' => 16, 'margin_bottom' => 18, 'tempDir' => storage_path('framework/cache')]);
        $context = $this->context->resolve($rows, collect());
        $pdf->SetTitle('Arsip Historis Kehadiran Seluruh Kelas Juli 2026');
        $pdf->SetAuthor('IMTAQ ISY KARIMA');
        $pdf->SetHTMLFooter('<div style="color:#65796f;font-size:8pt;text-align:right">IMTAQ ISY KARIMA · Rekap Juli 2026 · Halaman {PAGENO} dari {nbpg}</div>');
        $logoPath = public_path('images/logo-imtaq.png');
        $logo = is_file($logoPath) ? '<img class="logo" src="'.$logoPath.'" />' : '<strong class="org">IMTAQ ISY KARIMA</strong>';
        $html = '<style>body{font-family:dejavusans;color:#18352d;font-size:9pt}.masthead{border-bottom:1px solid #cfe3d6;padding-bottom:8px;margin-bottom:12px}.header-table{border-collapse:collapse;width:100%;margin:0}.header-table td{border:0;padding:0;vertical-align:middle}.logo-cell{width:27mm}.logo{height:18mm;width:auto}.org{color:#126b4d;font-weight:bold;font-size:11pt;letter-spacing:.4px}.eyebrow{color:#126b4d;font-size:9pt;font-weight:bold;letter-spacing:.6px;margin:0 0 5px;text-transform:uppercase}h1{color:#126b4d;font-size:18pt;margin:0 0 7px}p.meta{color:#527064;margin:0 0 4px;font-size:9pt}.rule{border-bottom:2px solid #126b4d;margin-bottom:12px}.summary{background:#f1f8f3;border:1px solid #cfe3d6;padding:9px 12px;margin-bottom:14px}.summary strong{color:#126b4d}table{border-collapse:collapse;width:100%;table-layout:fixed}thead{display:table-header-group}th{background:#126b4d;color:white;padding:8px 6px;text-align:left}th.num,td.num{text-align:right}.number{width:6%;text-align:center}th:nth-child(2){width:15%}th:nth-child(3),th:nth-child(4),th:nth-child(5),th:nth-child(6){width:8%}th:nth-child(7){width:14%}th:nth-child(8){width:14%}th:nth-child(9){width:16%}td{border-bottom:1px solid #d9e9df;padding:7px 6px}tr:nth-child(even) td{background:#f1f8f3}.total td{background:#d6ecdd!important;color:#126b4d;font-weight:bold}.note{color:#527064;font-size:8pt;margin-top:14px}.overall{background:#e8f5ed;border-left:4px solid #126b4d;color:#18352d;font-size:10pt;font-weight:bold;padding:9px 12px;margin-top:10px}.overall strong{color:#126b4d}</style>';
        $html .= '<div class="masthead"><table class="header-table"><tr><td class="logo-cell">'.$logo.'</td><td><p class="eyebrow">Arsip Historis Kegiatan Akademik</p><h1>Arsip Historis Kehadiran Seluruh Kelas — Juli 2026</h1><p class="meta">Semester I · Tahun Ajaran 2026/2027</p></td></tr></table></div><div class="rule"></div><div class="summary"><strong>Snapshot historis legacy · bukan data transaksi live</strong><br>'.$this->html($context['historical_notice']).'<br>'.$this->html($context['live_boundary_notice']).'<br>Sumber: '.$this->html($context['source_type']).' · '.$this->html($context['source_system']).' · '.$this->html($context['source_reference_label']).'<br>Jumlah santri: <strong>'.$rows->sum('roster').'</strong></div><table><thead><tr><th class="number">No</th><th>Kelas</th><th class="num">Hadir</th><th class="num">Izin</th><th class="num">Sakit</th><th class="num">Absen</th><th class="num">Kesempatan hadir dihitung</th><th class="num">Dikecualikan dari denominator</th><th class="num">% Kehadiran</th></tr></thead><tbody>';
        foreach ($rows as $index => $row) {
            $html .= '<tr><td class="number">'.($index + 1).'</td><td>'.$this->html(preg_replace('/\s*\(Pilot\)$/', '', $row->academicClass?->display_name ?? $row->class_id)).'</td><td class="num">'.$row->present.'</td><td class="num">'.$row->permission.'</td><td class="num">'.$row->sick.'</td><td class="num">'.$row->absent.'</td><td class="num">'.$row->eligible.'</td><td class="num">'.$row->non_eligible.'</td><td class="num">'.number_format((float) $row->attendance_rate * 100, 2, ',', '.').'%</td></tr>';
        }
        $totalRate = $rows->sum('eligible') > 0 ? ($rows->sum('present') / $rows->sum('eligible')) * 100 : 0;
        $html .= '<tr class="total"><td class="number">—</td><td>TOTAL</td><td class="num">'.$rows->sum('present').'</td><td class="num">'.$rows->sum('permission').'</td><td class="num">'.$rows->sum('sick').'</td><td class="num">'.$rows->sum('absent').'</td><td class="num">'.$rows->sum('eligible').'</td><td class="num">'.$rows->sum('non_eligible').'</td><td class="num">'.number_format($totalRate, 2, ',', '.').'%</td></tr></tbody></table><div class="overall">Persentase Kehadiran Seluruh Kelas Juli 2026: <strong>'.number_format($totalRate, 2, ',', '.').'%</strong></div><p class="note"><strong>Catatan:</strong> Kesempatan hadir dihitung adalah agregat eligible seluruh santri, bukan jumlah physical ClassSession. Dikecualikan dari denominator bukan termasuk absen. Snapshot ini tidak diperbarui otomatis oleh transaksi kehadiran live.</p>';
        $pdf->WriteHTML($html);

        return $pdf->Output('', 'S');
    }

    public function studentCsv(object $class, Collection $students): string
    {
        $context = $this->context->resolve(collect(), collect());
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Detail Arsip Kehadiran Historis Santri', $context['period_label'], 'Tahun Ajaran 2026/2027']);
        fputcsv($handle, ['Jenis laporan', 'Historical attendance snapshot']);
        fputcsv($handle, ['Source type', $context['source_type']]);
        fputcsv($handle, ['Source system', $context['source_system']]);
        fputcsv($handle, ['Live data', 'NO']);
        fputcsv($handle, [$context['historical_notice']]);
        fputcsv($handle, ['Kelas', $class->display_name]);
        fputcsv($handle, ['Santri', 'Nama Arab', 'Sumber', 'Hadir', 'Izin', 'Sakit', 'Absen', 'Keaktifan']);
        foreach ($students as $student) {
            fputcsv($handle, [$student->name_indonesia ?? '', $student->name_arabic ?? '', $student->source_record_id ?? '', $student->present, $student->permission, $student->sick, $student->absent, number_format((float) $student->attendance_rate * 100, 2, ',', '.').'%']);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    public function studentPdf(object $class, Collection $students): string
    {
        $context = $this->context->resolve(collect(), collect());
        $pdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 12, 'margin_right' => 12, 'margin_top' => 12, 'margin_bottom' => 14, 'tempDir' => storage_path('framework/cache')]);
        $pdf->SetTitle('Arsip Historis Kehadiran Santri Juli 2026');
        $pdf->SetAuthor('IMTAQ ISY KARIMA');
        $pdf->SetHTMLFooter('<div style="color:#65796f;font-size:8pt;text-align:right">IMTAQ ISY KARIMA · Rekap Juli 2026 · Halaman {PAGENO} dari {nbpg}</div>');
        $html = '<style>body{font-family:dejavusans;color:#18352d;font-size:8pt}.masthead{border-bottom:1px solid #cfe3d6;padding-bottom:5px;margin-bottom:8px}.header-table{border-collapse:collapse;width:100%;margin:0}.header-table td{border:0;padding:2mm 0;vertical-align:middle}.logo-cell{width:25mm}.logo{height:17mm;width:auto}.eyebrow{color:#126b4d;font-size:8pt;font-weight:bold;letter-spacing:.6px;margin:0 0 5px;text-transform:uppercase}h1{color:#126b4d;font-size:15pt;margin:0 0 7px}p.meta{color:#527064;margin:0 0 2px;font-size:8pt;line-height:1.5}.rule{border-bottom:2px solid #126b4d;margin-bottom:8px}table{border-collapse:collapse;width:100%}thead{display:table-header-group}th{background:#126b4d;color:white;padding:6px 4px;text-align:left}.number{width:9mm;text-align:center}td{border-bottom:1px solid #d9e9df;padding:8px 4px}tr:nth-child(even) td{background:#f1f8f3}.rate{color:#126b4d;font-weight:bold;text-align:right}.conclusion{background:#f1f8f3;border:1px solid #cfe3d6;border-left:4px solid #126b4d;padding:8px 10px;margin-top:12px}.conclusion strong{color:#126b4d}</style>';
        $logoPath = public_path('images/logo-imtaq.png');
        $logo = is_file($logoPath) ? '<img class="logo" src="'.$logoPath.'" />' : '<strong class="org">IMTAQ ISY KARIMA</strong>';
        $className = preg_replace('/\s*\(Pilot\)$/', '', $class->display_name ?? 'Kelas');
        $classRate = $students->sum('eligible') > 0 ? ($students->sum('present') / $students->sum('eligible')) * 100 : 0;
        $html .= '<div class="masthead"><table class="header-table"><tr><td class="logo-cell">'.$logo.'</td><td><p class="eyebrow">Arsip Historis Kegiatan Akademik</p><h1>Arsip Kehadiran '.$this->html($className).' — Juli 2026</h1><p class="meta">Semester I · Tahun Ajaran 2026/2027</p></td></tr></table></div><div class="rule"></div><div class="conclusion"><strong>Snapshot historis legacy · bukan data transaksi live</strong><br>'.$this->html($context['historical_notice']).'<br>Sumber: '.$this->html($context['source_type']).' · '.$this->html($context['source_system']).'</div><table><thead><tr><th class="number">No</th><th>Nama Santri</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Absen</th><th>Keaktifan</th></tr></thead><tbody>';
        foreach ($students as $index => $student) {
            $html .= '<tr><td class="number">'.($index + 1).'</td><td>'.$this->html($student->name_indonesia ?? '—').'</td><td>'.$student->present.'</td><td>'.$student->permission.'</td><td>'.$student->sick.'</td><td>'.$student->absent.'</td><td class="rate">'.number_format((float) $student->attendance_rate * 100, 2, ',', '.').'%</td></tr>';
        }
        $html .= '</tbody></table><div class="conclusion"><strong>Kesimpulan Kehadiran '.$this->html($className).'</strong><br>Persentase kehadiran kelas: <strong>'.number_format($classRate, 2, ',', '.').'%</strong></div>';
        $pdf->WriteHTML($html);

        return $pdf->Output('', 'S');
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
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $this->transliterateArabic($value)));
    }

    private function transliterateArabic(string $value): string
    {
        $map = ['ا' => 'a', 'أ' => 'a', 'إ' => 'i', 'آ' => 'a', 'ب' => 'b', 'ت' => 't', 'ث' => 'ts', 'ج' => 'j', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'dz', 'ر' => 'r', 'ز' => 'z', 'س' => 's', 'ش' => 'sy', 'ص' => 'sh', 'ض' => 'dh', 'ط' => 'th', 'ظ' => 'zh', 'ع' => '`', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q', 'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'ه' => 'h', 'و' => 'w', 'ي' => 'y', 'ى' => 'a', 'ة' => 'h', 'ء' => '`', 'ؤ' => 'u', 'ئ' => 'i', 'ـ' => '', 'َ' => '', 'ِ' => '', 'ُ' => '', 'ّ' => '', 'ْ' => '', 'ً' => '', 'ٍ' => '', 'ٌ' => ''];

        return strtr($value, $map);
    }

    private function html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
