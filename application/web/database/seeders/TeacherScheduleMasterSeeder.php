<?php

namespace Database\Seeders;

use App\Domains\Academic\Models\Subject;
use App\Shared\Core\Models\Staff;
use Illuminate\Database\Seeder;

class TeacherScheduleMasterSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'RAM' => 'الشيخ رمضان', 'RAS' => 'الشيخ رشيد', 'MUA' => 'الشيخ معمر',
            'ISM' => 'الشيخ عصام', 'SAR' => 'الأستاذ شرق', 'SAU' => 'الأستاذ صباري',
            'LUQ' => 'الأستاذ لقمان', 'WAW' => 'الأستاذ واوان', 'AZZ' => 'الأستاذ عزيز',
            'ARF' => 'الأستاذ عارفين', 'KHO' => 'الأستاذ خليف', 'FAQ' => 'الأستاذ فقيه',
            'ADT' => 'Aditya', 'DZK' => 'الأستاذ ذكي', 'ABU' => 'الأستاذ أبو عبد الله',
            'YSF' => 'الأستاذ يوسف', 'AKH' => 'Akhen',
        ] as $staffCode => $fullName) {
            Staff::query()->updateOrCreate(
                ['staff_code' => $staffCode],
                ['full_name' => $fullName, 'record_status' => 'ACTIVE', 'version_no' => 1]
            );
        }

        foreach ([
            'SUB-AQIDAH' => 'Aqidah', 'SUB-ARABIC' => 'Bahasa Arab',
            'SUB-ENTREPRENEUR' => 'Entrepreneurship', 'SUB-FIQH' => 'Fiqh',
            'SUB-FIQH-DAWAH' => 'Fiqh Dakwah', 'SUB-HADITH' => 'Hadits',
            'SUB-JAZARIYYAH' => 'Jazariyyah', 'SUB-MEDIA' => "I'lam / Media",
            'SUB-MUSTALAH' => 'Musthalah Hadits', 'SUB-NAHWU' => 'Nahwu',
            'SUB-SIRAH' => 'Sirah', 'SUB-TAFSIR' => 'Tafsir',
            'SUB-ULUM-QH' => "Ulumul Qur'an & Hadits", 'SUB-ULUM-QURAN' => "Ulumul Qur'an",
            'SUB-USUL-FIQH' => 'Ushul Fiqh',
        ] as $subjectCode => $subjectName) {
            Subject::query()->updateOrCreate(
                ['subject_code' => $subjectCode],
                ['subject_name' => $subjectName, 'status' => 'ACTIVE', 'version_no' => 1]
            );
        }
    }
}
