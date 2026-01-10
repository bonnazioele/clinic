<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation - CliniQ</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 20px; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Print Buttons -->
            <div class="no-print mb-4 flex justify-between">
                <a href="/walkin" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Register Another Patient
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Print Registration Slip
                </button>
            </div>

            <!-- Registration Confirmation -->
            <div class="bg-white shadow-lg rounded-lg p-8">
                <!-- Header -->
                <div class="text-center border-b-2 border-blue-600 pb-6 mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">CliniQ</h1>
                    <p class="text-lg text-gray-600 mt-2">Walk-In Patient Registration</p>
                    <div class="mt-4 inline-block px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold">
                        ✓ Registration Successful
                    </div>
                </div>

                <!-- Visit Information -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Visit Number</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $visit->visit_number }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Patient Number</p>
                        <p class="text-2xl font-bold text-green-600">{{ $visit->patient->patient_number }}</p>
                    </div>
                </div>

                <!-- Patient Information -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 border-b pb-2">Patient Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Full Name</p>
                            <p class="font-semibold">{{ $visit->patient->full_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Sex</p>
                            <p class="font-semibold">{{ $visit->patient->sex }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Age</p>
                            <p class="font-semibold">{{ $visit->patient->age }} years old</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Date of Birth</p>
                            <p class="font-semibold">{{ $visit->patient->date_of_birth->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Mobile Number</p>
                            <p class="font-semibold">{{ $visit->patient->mobile_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email Address</p>
                            <p class="font-semibold">{{ $visit->patient->email_address ?: 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Visit Details -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 border-b pb-2">Visit Details</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Date of Visit</p>
                            <p class="font-semibold">{{ $visit->date_of_visit->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Time In</p>
                            <p class="font-semibold">{{ $visit->time_in->format('h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Visit Type</p>
                            <p class="font-semibold">{{ $visit->visit_type }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Patient Type</p>
                            <p class="font-semibold">{{ $visit->patient_type }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Requested Service</p>
                            <p class="font-semibold">{{ $visit->requested_service }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Priority Level</p>
                            <p class="font-semibold">
                                <span class="px-3 py-1 rounded-full text-sm
                                    @if($visit->priority_level === 'Emergency') bg-red-100 text-red-800
                                    @elseif($visit->priority_level === 'Urgent') bg-orange-100 text-orange-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ $visit->priority_level }}
                                </span>
                            </p>
                        </div>
                        @if($visit->assigned_department)
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Assigned Department</p>
                            <p class="font-semibold">{{ $visit->assigned_department }}</p>
                        </div>
                        @endif
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Reason for Visit</p>
                            <p class="font-semibold">{{ $visit->reason_for_visit }}</p>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 border-b pb-2">Emergency Contact</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-semibold">{{ $visit->patient->emergency_contact_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Relationship</p>
                            <p class="font-semibold">{{ $visit->patient->emergency_contact_relationship }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Contact Number</p>
                            <p class="font-semibold">{{ $visit->patient->emergency_contact_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Registration Details -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Registered By</p>
                            <p class="font-semibold">{{ $visit->registrationStaff->name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Registration Date</p>
                            <p class="font-semibold">{{ $visit->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Instructions -->
                <div class="mt-8 p-4 bg-blue-50 border-l-4 border-blue-600 rounded">
                    <p class="text-sm text-gray-700">
                        <strong>Important:</strong> Please keep this registration slip for your records.
                        Present this or provide your visit number when called for your {{ $visit->requested_service }}.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
