<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Walk-In Registration Settings
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the Walk-In Patient Registration module
    |
    */

    'patient_number_prefix' => env('PATIENT_NUMBER_PREFIX', 'PAT'),
    'visit_number_prefix' => env('VISIT_NUMBER_PREFIX', 'VST'),

    'visit_types' => [
        'Walk-In',
        'Follow-Up',
    ],

    'requested_services' => [
        'Consultation',
        'Check-up',
        'Medical Certificate',
        'Laboratory',
        'Vaccination',
    ],

    'patient_types' => [
        'New',
        'Returning',
    ],

    'priority_levels' => [
        'Normal',
        'Urgent',
        'Emergency',
    ],

    'visit_statuses' => [
        'Registered',
        'In Progress',
        'Completed',
        'Cancelled',
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Privacy Compliance
    |--------------------------------------------------------------------------
    |
    | Settings related to healthcare data privacy compliance
    |
    */

    'data_retention_days' => env('DATA_RETENTION_DAYS', 365 * 10), // 10 years default
    'require_consent' => true,
    'require_signature' => true,

    /*
    |--------------------------------------------------------------------------
    | Departments
    |--------------------------------------------------------------------------
    |
    | Available departments for patient assignment
    |
    */

    'departments' => [
        'General Medicine',
        'Pediatrics',
        'Obstetrics and Gynecology',
        'Surgery',
        'Emergency Department',
        'Laboratory',
        'Radiology',
        'Pharmacy',
    ],

    /*
    |--------------------------------------------------------------------------
    | Emergency Contact Relationships
    |--------------------------------------------------------------------------
    |
    | Common relationships for emergency contacts
    |
    */

    'emergency_relationships' => [
        'Spouse',
        'Parent',
        'Child',
        'Sibling',
        'Relative',
        'Friend',
        'Guardian',
        'Other',
    ],
];
