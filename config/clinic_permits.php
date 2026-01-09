<?php

return [
    'types' => [
        [
            'key' => 'business_permit',
            'label' => 'Local Business Permit',
            'description' => 'Issued by the LGU to certify the clinic is allowed to operate within the municipality or city.',
            'requires_number' => true,
            'requires_issue_date' => true,
            'requires_expiry_date' => true,
        ],
        [
            'key' => 'doh_license',
            'label' => 'Department of Health License',
            'description' => 'Proof of accreditation or license issued by the Department of Health.',
            'requires_number' => true,
            'requires_issue_date' => true,
            'requires_expiry_date' => true,
        ],
        [
            'key' => 'sanitary_permit',
            'label' => 'Sanitary Permit',
            'description' => 'Sanitary permit or clearance granted by the local government health office.',
            'requires_number' => false,
            'requires_issue_date' => true,
            'requires_expiry_date' => true,
        ],
    ],
];
