<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Slip - CliniQ</title>
    @vite(['resources/sass/app.scss'])
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .signature-box {
            border-bottom: 1px solid #9ca3af;
            height: 60px;
            width: 100%;
        }
        @media print {
            @page {
                margin: 20mm;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-3xl mx-auto px-4">
            <!-- Print Buttons -->
            <div class="no-print mb-4 flex justify-between">
                <a href="/walkin/today" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Back to Today’s Registrations
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Print Slip
                </button>
            </div>

            <!-- Registration Slip -->
            <div class="bg-white shadow-lg rounded-lg p-8">
                <!-- Header -->
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">CliniQ</h1>
                    <p class="text-gray-600">Walk-In Patient Registration Slip</p>
                    <div class="mt-2 text-sm text-gray-500">{{ $visit->date_of_visit->format('F d, Y') }} | {{ $visit->time_in->format('h:i A') }}</div>
                </div>

                <!-- Registration Details -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Visit Number</p>
                        <p class="text-xl font-semibold text-blue-600">{{ $visit->visit_number }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Patient Number</p>
                        <p class="text-xl font-semibold text-green-600">{{ $visit->patient->patient_number }}</p>
                    </div>
                </div>

                <!-- Patient & Visit Info -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-600">Patient Name</p>
                        <p class="font-semibold text-gray-900">{{ $visit->patient->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Date of Birth</p>
                        <p class="font-semibold text-gray-900">{{ $visit->patient->date_of_birth->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Contact Number</p>
                        <p class="font-semibold text-gray-900">{{ $visit->patient->mobile_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Patient Type</p>
                        <p class="font-semibold text-gray-900">{{ $visit->patient_type }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-600">Requested Service</p>
                        <p class="font-semibold text-gray-900">{{ $visit->requested_service }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Priority Level</p>
                        <p class="font-semibold text-gray-900">{{ $visit->priority_level }}</p>
                    </div>
                    @if($visit->assigned_department)
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Assigned Department</p>
                        <p class="font-semibold text-gray-900">{{ $visit->assigned_department }}</p>
                    </div>
                    @endif
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Reason for Visit / Chief Complaint</p>
                        <p class="font-semibold text-gray-900">{{ $visit->reason_for_visit }}</p>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="border rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase mb-3">Emergency Contact</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Name</p>
                            <p class="font-semibold">{{ $visit->patient->emergency_contact_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Relationship</p>
                            <p class="font-semibold">{{ $visit->patient->emergency_contact_relationship }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Contact Number</p>
                            <p class="font-semibold">{{ $visit->patient->emergency_contact_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Consent & Signatures -->
                <div class="border rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase mb-3">Consent & Verification</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        The patient has consented to the collection, processing, and storage of personal and medical information in accordance with the Data Privacy Act of 2012 (RA 10173).
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500">Patient Signature</p>
                            @if($visit->patient_signature)
                            <img src="{{ $visit->patient_signature }}" alt="Signature" class="signature-box">
                            @else
                            <div class="signature-box"></div>
                            @endif
                            <p class="text-xs text-gray-500 mt-2">Date Signed: {{ $visit->date_signed->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Registered By</p>
                            <p class="font-semibold">{{ $visit->registrationStaff->name }}</p>
                            <div class="signature-box mt-4"></div>
                            <p class="text-xs text-gray-500 mt-2">Signature</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center text-xs text-gray-500">
                    Please present this slip at the reception when called. For follow-up inquiries, contact CliniQ at (xxx) xxx-xxxx.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
