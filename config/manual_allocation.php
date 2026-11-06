<?php

/*
 * Spelling fixes for the Ministry's allocation workbooks (a sheet per region, with zone
 * columns or a sheet per zone; one row per student text or teacher guide). Keys are matched case-insensitively after
 * trimming and collapsing spaces; anything not listed is kept as written. Add a line here
 * when a new spelling shows up instead of editing the import code.
 */
return [

    'regions' => [
        'benshangul gumuz' => 'Benishangul Gumuz',
        'benishangul' => 'Benishangul Gumuz',
        'beneshangul' => 'Benishangul Gumuz',
        'centralethiopia' => 'Central Ethiopia',
        'southethiopia' => 'South Ethiopia',
        'diredawa' => 'Dire Dawa',
        'south west' => 'South West Ethiopia',
        'southwest' => 'South West Ethiopia',
        'swepr' => 'South West Ethiopia',
        'southwesteth' => 'South West Ethiopia',
        'swe' => 'South West Ethiopia',
    ],

    // Zone sheets and zone columns are named after their zone; a zone sheet's A1 caption is
    // tried when its name is not a zone. Local names of zones already set up map onto them.
    'zones' => [
        'kafa' => 'Kaffa',
        'semen showa' => 'North Shewa Zone',
        'mirab gojjam' => 'West Gojjam Zone',
        'shawaa bahaa' => 'East Shewa Zone',
        'godina jimma' => 'Jimma Zone',
        'arsii lixaa' => 'West Arsi Zone',
    ],

    // Headers right of Final Quantity that are not zones
    'ignored_columns' => ['remark', 'remarks', 'cover color', 'cover colour', 'note', 'notes'],

    'languages' => [
        'englishi' => 'English',
        'majange' => 'Majang',
        'wolaitatto' => 'Wolaytatto',
        'afar afi' => 'Afar Af',
        'afu oromo' => 'Afaan Oromo',
        'aff-oromo' => 'Afaan Oromo',
        'afan oromo' => 'Afaan Oromo',
        'af-somali' => 'Af Soomaali',
        'in amharic' => 'Amharic',
        'hp amharic' => 'Amharic',
        'sa- gumuza' => 'Sa-Gumuza',
        'tembarisssa' => 'Tembarissa',
        'kaffinon' => 'Kafinoono',
        'kaffnon' => 'Kafinoono',
        'kafinono' => 'Kafinoono',
        "me'en" => "Me'en",
    ],

    // Subject words some sheets append to the language ("KaffNon Moral", "Me'en Pre prim")
    'language_suffix_pattern' => '/\s+(env|moral|mt|maths?l?|pre\s*prim)$/i',

    'subjects' => [
        'mother toung' => 'Mother Tongue',
        'mother tounge' => 'Mother Tongue',
        'health physical education' => 'Health and Physical Education',
        'hp' => 'Health and Physical Education',
        'hpe' => 'Health and Physical Education',
        'art' => 'Performance and Visual Art',
        'ahmaric' => 'Amharic',
        'amahric 2nd language' => 'Amharic',
        'amharic as second language' => 'Amharic',
        'afan oromo' => 'Afaan Oromo',
        'preprimary st' => 'Integrated Pre-Primary',
        'preprimary tg' => 'Integrated Pre-Primary',
        'environmental sience' => 'Environmental Science',
        'sidaamu afoo' => 'Mother Tongue',
        'amharic as 1st languge' => 'Mother Tongue',
        'amharic as 1st language' => 'Mother Tongue',
        'amharic as 2nd languge' => 'Mother Tongue',
        'amharic as 2nd language' => 'Mother Tongue',
        'pva' => 'Performance and Visual Art',
    ],

    // Title patterns that name the subject when a row's subject cell is empty, checked in order
    'subject_title_patterns' => [
        '/\b(fil|first language|mother tongue)\b/i' => 'Mother Tongue',
        '/\b(2nd|second) langu/i' => 'Mother Tongue',
        '/pre-?\s*primary/i' => 'Integrated Pre-Primary',
        '/\benv[ie]?r[oa]?n?mental\b/i' => 'Environmental Science',
        '/\bpva|performance and visual/i' => 'Performance and Visual Art',
        '/\bhpe\b|physical education/i' => 'Health and Physical Education',
        '/\bmoral\b/i' => 'Moral Education',
        '/\bmath/i' => 'Mathematics',
        '/\benglish\b/i' => 'English',
    ],

    // Subject cells that only say which language slot the book fills: the row's language is the subject
    'language_subjects' => ['2nd language'],

    'grades' => [
        'l1' => 'KG L1',
        'l2' => 'KG L2',
        'l3' => 'KG L3',
        'level 1' => 'KG L1',
        'level 2' => 'KG L2',
        'level 3' => 'KG L3',
    ],

    // A TBD or blank type is taken as a student text (STB), unless the title has one of these words
    'teacher_keywords' => ['teacher', 'guide', 't.guid', 'tg'],

    // Title words that mark a second-language edition of the same subject and grade
    'second_language_pattern' => '/\b(2nd|second)\b/i',

    /*
     * Regional bureau woreda splits (manual:import-woreda-allocations): a sheet per subject,
     * zone and woreda in the first two columns, then a student text and teacher guide column
     * per grade. A sheet is matched to the region's allocated titles by the first keyword
     * found in its name or caption; the titles are those whose subject is one of the listed
     * names (as the allocation import stored them) and whose grade matches the column.
     */
    'woreda_sheets' => [
        'pre-primary' => ['Integrated Pre-Primary'],
        'pre primary' => ['Integrated Pre-Primary'],
        'afan oromo' => ['Mother Tongue'],
        'afaan oromoo' => ['Mother Tongue'],
        'herreegaa' => ['Mathematics'],
        'math' => ['Mathematics'],
        'english' => ['English'],
        'saayinsii' => ['Environmental Science'],
        'environmental' => ['Environmental Science'],
        'gadaa' => ['Moral Education'],
        'amharic' => ['Amahric 2nd Language', 'Amharic 2nd Language', 'Amharic'],
        'afaan amaaraa' => ['Amahric 2nd Language', 'Amharic 2nd Language', 'Amharic'],
    ],

    // Zone cells carry the woreda count after the name ("Arsii  Aanaa 28", "Magaala Shaggar Kutaa Magaalaa 12")
    'woreda_zone_suffix_pattern' => '/\s+(aanaa|aanna|kutaa\s+magaalaa|woredas?)\b.*$|\s+\d+\s*$/iu',

    // Rows that are not woredas: zone subtotals and the grand total
    'woreda_skip_rows' => ['idaáma', 'idaama', 'total', 'grand total', 'sub total', 'subtotal'],

    // Woreda spellings that differ between sheets, mapped onto one name (keys lower-case)
    'woredas' => [],

    /*
     * The printer's data (manual:import-printer-data), one workbook per region: 1_Books (a row
     * per book edition with its QR code number and QR text), 2_Zone_Allocation (copies per zone
     * and title) and 3_Package_List (a row per carton). Subjects are written "MT(Language)",
     * "Second Language(Language)" or "Subject(Variant)"; the subject and language aliases above
     * tidy them. Zone names may end in the woreda count ("Arsii Aanaa 28"), which is dropped.
     */
    'printer' => [
        'mother_tongue' => ['mt', 'mother tongue'],
        'second_language' => ['second language', '2nd language'],
        'zone_suffix_pattern' => '/\s+(aanaa|aanna|kutaa\s+magaalaa)\b.*$|\s+\d+\s*$/iu',
        // Short names used in carton package codes
        'carton_types' => [
            'full' => 'F',
            'student only' => 'S',
            'teacher guide only' => 'T',
            'partial' => 'P',
            'student only partial' => 'SP',
            'teacher guide only partial' => 'TP',
        ],
    ],
];
