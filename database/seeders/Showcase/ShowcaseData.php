<?php

namespace Database\Seeders\Showcase;

/**
 * Static reference data for the showcase seed profile.
 *
 * Regions, zones and woredas are real Ethiopian administrative units with approximate
 * coordinates and landline area codes. Schools, people and quantities are fictional.
 */
final class ShowcaseData
{
    public const BOOKS_PER_PACKAGE = 40;

    public const PASSWORD = 'test1234';

    public const EMAIL_DOMAIN = 'tracktrace.test';

    public const PRINTERS = [
        'primary' => [
            'name' => 'Educational Materials Production and Distribution Enterprise',
            'short' => 'EMPDE',
            'branch' => 1001,
            'year' => 1976,
        ],
        'secondary' => [
            'name' => 'Berhanena Selam Printing Enterprise',
            'short' => 'Berhanena Selam',
            'branch' => 1002,
            'year' => 1921,
        ],
    ];

    public const MINISTRY = [
        'name' => 'Ministry of Education',
        'short' => 'MoE',
        'branch' => 1100,
        'year' => 1930,
    ];

    /**
     * region => [code, is_city, bureau, zones => [zone => [code, is_subcity, office, woredas => [woreda => meta]]]]
     */
    public const GEOGRAPHY = [
        'Addis Ababa' => [
            'code' => 'AA',
            'is_city' => true,
            'bureau' => 'Addis Ababa City Administration Education Bureau',
            'zones' => [
                'Bole Subcity' => [
                    'code' => 'BS01',
                    'is_subcity' => true,
                    'office' => 'Bole Sub-City Education Office',
                    'woredas' => [
                        'Bole Woreda 01' => ['code' => 'BW01', 'gps' => [8.9950, 38.7880], 'area' => '011', 'primary' => 'Andinet Primary School', 'secondary' => 'Bole Secondary School'],
                        'Bole Woreda 03' => ['code' => 'BW03', 'gps' => [9.0050, 38.8050], 'area' => '011', 'primary' => 'Edget Primary School', 'secondary' => 'Bole Preparatory School'],
                    ],
                ],
                'Kirkos Subcity' => [
                    'code' => 'KS01',
                    'is_subcity' => true,
                    'office' => 'Kirkos Sub-City Education Office',
                    'woredas' => [
                        'Kirkos Woreda 02' => ['code' => 'KW02', 'gps' => [9.0100, 38.7600], 'area' => '011', 'primary' => 'Tesfa Kokeb Primary School', 'secondary' => 'Kirkos Secondary School'],
                    ],
                ],
                'Yeka Subcity' => [
                    'code' => 'YS01',
                    'is_subcity' => true,
                    'office' => 'Yeka Sub-City Education Office',
                    'woredas' => [
                        'Yeka Woreda 08' => ['code' => 'YW08', 'gps' => [9.0400, 38.8200], 'area' => '011', 'primary' => 'Birhan Primary School', 'secondary' => 'Yeka Secondary School'],
                    ],
                ],
            ],
        ],
        'Oromia' => [
            'code' => 'OR',
            'is_city' => false,
            'bureau' => 'Oromia Education Bureau',
            'zones' => [
                'East Shewa Zone' => [
                    'code' => 'ESZ',
                    'is_subcity' => false,
                    'office' => 'East Shewa Zone Education Department',
                    'woredas' => [
                        'Adama Woreda 01' => ['code' => 'AW01', 'gps' => [8.5400, 39.2700], 'area' => '022', 'primary' => 'Ifa Bula Primary School', 'secondary' => 'Adama Secondary School'],
                        'Lume Woreda' => ['code' => 'LMW', 'gps' => [8.5900, 39.1200], 'area' => '022', 'primary' => 'Bilisuma Primary School', 'secondary' => 'Mojo Secondary School'],
                    ],
                ],
                'Jimma Zone' => [
                    'code' => 'JMZ',
                    'is_subcity' => false,
                    'office' => 'Jimma Zone Education Department',
                    'woredas' => [
                        'Seka Chekorsa Woreda' => ['code' => 'SCW', 'gps' => [7.5900, 36.7300], 'area' => '047', 'primary' => 'Abdi Boru Primary School', 'secondary' => 'Seka Secondary School'],
                        'Mana Woreda' => ['code' => 'MNW', 'gps' => [7.7700, 36.8700], 'area' => '047', 'primary' => 'Gadaa Primary School', 'secondary' => 'Yebu Secondary School'],
                    ],
                ],
                'West Arsi Zone' => [
                    'code' => 'WAZ',
                    'is_subcity' => false,
                    'office' => 'West Arsi Zone Education Department',
                    'woredas' => [
                        'Shashemene Zuria Woreda' => ['code' => 'SZW', 'gps' => [7.2000, 38.6000], 'area' => '046', 'primary' => 'Hawi Primary School', 'secondary' => 'Shashemene Zuria Secondary School'],
                    ],
                ],
            ],
        ],
        'Amhara' => [
            'code' => 'AM',
            'is_city' => false,
            'bureau' => 'Amhara Education Bureau',
            'zones' => [
                'North Shewa Zone' => [
                    'code' => 'NSZ',
                    'is_subcity' => false,
                    'office' => 'North Shewa Zone Education Department',
                    'woredas' => [
                        'Debre Berhan Woreda 01' => ['code' => 'DBW01', 'gps' => [9.6800, 39.5300], 'area' => '011', 'primary' => 'Hibret Primary School', 'secondary' => 'Debre Berhan Secondary School'],
                        'Ankober Woreda' => ['code' => 'ANW', 'gps' => [9.5700, 39.7300], 'area' => '011', 'primary' => 'Fana Primary School', 'secondary' => 'Ankober Secondary School'],
                    ],
                ],
                'West Gojjam Zone' => [
                    'code' => 'WGZ',
                    'is_subcity' => false,
                    'office' => 'West Gojjam Zone Education Department',
                    'woredas' => [
                        'Mecha Woreda' => ['code' => 'MCW', 'gps' => [11.4100, 37.1600], 'area' => '058', 'primary' => 'Tsehay Primary School', 'secondary' => 'Merawi Secondary School'],
                        'Bahir Dar Zuria Woreda' => ['code' => 'BDZ', 'gps' => [11.5900, 37.3900], 'area' => '058', 'primary' => 'Netsanet Primary School', 'secondary' => 'Bahir Dar Zuria Secondary School'],
                    ],
                ],
            ],
        ],
    ];

    /**
     * Student textbooks per grade. Grades 1-8 are served by primary schools, 9-12 by secondary.
     */
    public const CURRICULUM = [
        '1' => ['Amharic', 'English', 'Mathematics', 'Environmental Sience'],
        '2' => ['Amharic', 'English', 'Mathematics', 'Environmental Sience'],
        '3' => ['Amharic', 'English', 'Mathematics', 'Environmental Sience'],
        '4' => ['Amharic', 'English', 'Mathematics', 'Environmental Sience'],
        '5' => ['Amharic', 'English', 'Mathematics', 'Social Studies'],
        '6' => ['Amharic', 'English', 'Mathematics', 'Social Studies'],
        '7' => ['English', 'Mathematics', 'Civics', 'Social Studies', 'ICT'],
        '8' => ['English', 'Mathematics', 'Civics', 'Social Studies', 'ICT'],
        '9' => ['English', 'Mathematics', 'Physics', 'Chemistry', 'Biology', 'History', 'Geography', 'ICT'],
        '10' => ['English', 'Mathematics', 'Physics', 'Chemistry', 'Biology', 'History', 'Geography', 'ICT'],
        '11' => ['English', 'Mathematics', 'Physics', 'Chemistry', 'Biology', 'ICT', 'General Business'],
        '12' => ['English', 'Mathematics', 'Physics', 'Chemistry', 'Biology', 'ICT', 'General Business'],
    ];

    /**
     * Base enrolment per section of a grade; each school applies its own size factor.
     */
    public const ENROLMENT = [
        '1' => 160, '2' => 148, '3' => 138, '4' => 128,
        '5' => 118, '6' => 108, '7' => 98, '8' => 92,
        '9' => 225, '10' => 205, '11' => 155, '12' => 140,
    ];

    /**
     * How far each grade's books have travelled. The last leg can be "in transit":
     * dispatched by the holder but not yet received downstream.
     *
     * Stages: requested, accepted, printed, moe, region, zone, woreda, school.
     */
    public const GRADE_PROGRESS = [
        '1' => ['stage' => 'school', 'transit' => false],
        '2' => ['stage' => 'school', 'transit' => false],
        '3' => ['stage' => 'school', 'transit' => false],
        '4' => ['stage' => 'school', 'transit' => false],
        '5' => ['stage' => 'school', 'transit' => false],
        '6' => ['stage' => 'school', 'transit' => false],
        '7' => ['stage' => 'woreda', 'transit' => true],
        '8' => ['stage' => 'woreda', 'transit' => false],
        '9' => ['stage' => 'school', 'transit' => false],
        '10' => ['stage' => 'zone', 'transit' => true],
        '11' => ['stage' => 'region', 'transit' => false],
        '12' => ['stage' => 'moe', 'transit' => true],
    ];

    /**
     * Individual books that lag behind their grade.
     */
    public const BOOK_PROGRESS_OVERRIDES = [
        '11|General Business' => ['stage' => 'printed', 'transit' => false],
        '12|ICT' => ['stage' => 'printed', 'transit' => false],
    ];

    /**
     * Extra print orders that never reached printing.
     */
    public const PENDING_ORDERS = [
        ['grade' => '1', 'subject' => 'Amharic', 'copies' => 2400, 'status' => 0, 'days_ago' => 2, 'note' => 'Replacement copies for flood-damaged stock in East Shewa and Jimma zones.'],
        ['grade' => '5', 'subject' => 'Mathematics', 'copies' => 1600, 'status' => 1, 'days_ago' => 9, 'note' => 'Supplementary order for enrolment growth reported by Oromia Education Bureau.'],
        ['grade' => '12', 'subject' => 'English', 'copies' => 3200, 'status' => 4, 'days_ago' => 14, 'note' => 'Rejected: duplicates the approved annual order for this title.'],
    ];

    /**
     * Already-printed books tracked by quantity (manual tracking). QR text follows the
     * printed format, e.g. "Oromia Grade 6 Mathematics Teacher guide".
     */
    public const MANUAL_TITLES = [
        ['region' => 'Oromia', 'grade' => '3', 'subject' => 'Mathematics', 'teacher_guide' => true],
        ['region' => 'Oromia', 'grade' => '6', 'subject' => 'Mathematics', 'teacher_guide' => true],
        ['region' => 'Oromia', 'grade' => '6', 'subject' => 'English', 'teacher_guide' => true],
        ['region' => 'Oromia', 'grade' => '8', 'subject' => 'Social Studies', 'teacher_guide' => true],
        ['region' => 'Amhara', 'grade' => '3', 'subject' => 'Amharic', 'teacher_guide' => true],
        ['region' => 'Amhara', 'grade' => '6', 'subject' => 'Mathematics', 'teacher_guide' => true],
        ['region' => 'Amhara', 'grade' => '8', 'subject' => 'English', 'teacher_guide' => true],
        ['region' => 'Addis Ababa', 'grade' => '1', 'subject' => 'Sign Language', 'teacher_guide' => false],
        ['region' => 'Addis Ababa', 'grade' => '6', 'subject' => 'Amharic', 'teacher_guide' => true],
        ['region' => 'Addis Ababa', 'grade' => '6', 'subject' => 'Mathematics', 'teacher_guide' => true],
    ];

    /**
     * Where printed books enter the country before reaching a warehouse.
     */
    public const LOGISTICS_HUBS = [
        'port' => [
            'name' => 'Djibouti Port Logistics Office',
            'country' => ['name' => 'Djibouti', 'code' => 'DJ'],
            'region' => null,
            'gps' => [11.6000, 43.1400],
            'branch' => 1502,
            'email' => 'djibouti.port',
            'carrier' => 'Ethiopian Shipping & Logistics',
        ],
        'cargo' => [
            'name' => 'Ethiopian Airlines Cargo Terminal',
            'country' => ['name' => 'Ethiopia', 'code' => 'ET'],
            'region' => 'Addis Ababa',
            'gps' => [8.9779, 38.7993],
            'branch' => 1501,
            'email' => 'cargo.terminal',
            'carrier' => 'Ethiopian Airlines Cargo',
        ],
    ];

    /**
     * Route and progress of each region's manual books. Levels: hub, moe, region, zone, woreda, school.
     * With `transit`, the leg after `reached` is dispatched but not yet received.
     */
    public const MANUAL_ROUTING = [
        'Oromia' => ['hub' => 'port', 'via_moe' => false, 'reached' => 'school', 'transit' => false, 'days_ago' => 75],
        'Amhara' => ['hub' => 'port', 'via_moe' => false, 'reached' => 'woreda', 'transit' => true, 'days_ago' => 1],
        'Addis Ababa' => ['hub' => 'cargo', 'via_moe' => true, 'reached' => 'region', 'transit' => true, 'days_ago' => 1],
    ];

    public const FIRST_NAMES = [
        'Abebe', 'Hana', 'Selam', 'Dawit', 'Meron', 'Yonas', 'Tsion', 'Kalkidan', 'Biruk', 'Liya',
        'Natnael', 'Bethlehem', 'Eyerusalem', 'Samuel', 'Mahlet', 'Robel', 'Hiwot', 'Ermias', 'Rahel', 'Fikirte',
        'Abdi', 'Chaltu', 'Gemechu', 'Lensa', 'Tolera', 'Ayantu', 'Obsa', 'Hawi', 'Kenenisa', 'Bontu',
        'Tewodros', 'Mekdes', 'Getnet', 'Tigist', 'Solomon', 'Almaz', 'Mulugeta', 'Hirut', 'Tsegaye', 'Eden',
    ];

    public const FATHER_NAMES = [
        'Kebede', 'Tesfaye', 'Girma', 'Alemu', 'Haile', 'Bekele', 'Mulugeta', 'Assefa', 'Tadesse', 'Getachew',
        'Worku', 'Desta', 'Abate', 'Negash', 'Fikru', 'Tilahun', 'Demissie', 'Wolde', 'Ayele', 'Gebre',
        'Mohammed', 'Tolosa', 'Fayisa', 'Dinka', 'Gudeta', 'Lemma', 'Jemal', 'Mekonnen', 'Yilma', 'Shiferaw',
    ];
}
