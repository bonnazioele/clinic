<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Walk-In Patient Registration - CliniQ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .signature-pad {
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            cursor: crosshair;
            touch-action: none;
        }
        .required::after {
            content: " *";
            color: #ef4444;
        }
        .section-title {
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">CliniQ</h1>
                        <p class="text-gray-600 mt-1">Walk-In Patient Registration Module</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Date: {{ date('F d, Y') }}</p>
                        <p class="text-sm text-gray-500">Time: {{ date('h:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <form id="registrationForm" class="space-y-6">
                @csrf

                <!-- Patient Lookup Section -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="section-title">Patient Lookup</h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Search Existing Patient (Name, Mobile Number, or Patient ID)
                        </label>
                        <div class="relative">
                            <input type="text" id="patientSearch"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Search for returning patient...">
                            <div id="searchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden max-h-60 overflow-y-auto shadow-lg">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="patient_id" name="patient_id">
                </div>

                <!-- Patient Personal Information -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="section-title">Patient Personal Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Last Name</label>
                            <input type="text" name="last_name" id="last_name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">First Name</label>
                            <input type="text" name="first_name" id="first_name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                            <input type="text" name="middle_name" id="middle_name"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Sex</label>
                            <select name="sex" id="sex" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Age (Auto-calculated)</label>
                            <input type="text" id="age" readonly
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="section-title">Contact Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Mobile Number</label>
                            <input type="tel" name="mobile_number" id="mobile_number" required
                                   placeholder="09XXXXXXXXX"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email_address" id="email_address"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Complete Address</label>
                        <textarea name="complete_address" id="complete_address" rows="3" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <!-- Visit Information -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="section-title">Visit Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Visit Type</label>
                            <select name="visit_type" id="visit_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Visit Type</option>
                                <option value="Walk-In">Walk-In</option>
                                <option value="Follow-Up">Follow-Up</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Requested Service</label>
                            <select name="requested_service" id="requested_service" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Service</option>
                                <option value="Consultation">Consultation</option>
                                <option value="Check-up">Check-up</option>
                                <option value="Medical Certificate">Medical Certificate</option>
                                <option value="Laboratory">Laboratory</option>
                                <option value="Vaccination">Vaccination</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Reason for Visit / Chief Complaint</label>
                        <textarea name="reason_for_visit" id="reason_for_visit" rows="3" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Department / Unit</label>
                        <input type="text" name="assigned_department" id="assigned_department"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Patient Classification -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="section-title">Patient Classification</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Patient Type</label>
                            <select name="patient_type" id="patient_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Patient Type</option>
                                <option value="New">New Patient</option>
                                <option value="Returning">Returning Patient</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Priority Level</label>
                            <select name="priority_level" id="priority_level" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Priority</option>
                                <option value="Normal">Normal</option>
                                <option value="Urgent">Urgent</option>
                                <option value="Emergency">Emergency</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="section-title">Emergency Contact</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Contact Name</label>
                            <input type="text" name="emergency_contact_name" id="emergency_contact_name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Relationship</label>
                            <input type="text" name="emergency_contact_relationship" id="emergency_contact_relationship" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Contact Number</label>
                            <input type="tel" name="emergency_contact_number" id="emergency_contact_number" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Consent & Verification -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="section-title">Consent & Verification</h2>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-blue-900 mb-2">Data Privacy Notice</h3>
                        <p class="text-sm text-blue-800">
                            By providing your information and signature below, you consent to the collection, processing,
                            and storage of your personal and medical data in accordance with the Data Privacy Act of 2012
                            (RA 10173) and applicable healthcare regulations. Your information will be used solely for
                            healthcare delivery, record-keeping, and clinic operations. We are committed to protecting
                            your privacy and maintaining the confidentiality of your health information.
                        </p>
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" name="consent_to_data_collection" id="consent_to_data_collection" required
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm font-medium text-gray-700 required">
                                I consent to the collection, processing, and storage of my personal and medical data
                            </span>
                        </label>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Patient Signature</label>
                        <div class="border-2 border-gray-300 rounded-lg p-2 bg-white">
                            <canvas id="signaturePad" class="signature-pad" width="600" height="200"></canvas>
                        </div>
                        <div class="mt-2 flex justify-end">
                            <button type="button" id="clearSignature"
                                    class="px-4 py-2 text-sm text-red-600 hover:text-red-800 font-medium">
                                Clear Signature
                            </button>
                        </div>
                        <input type="hidden" name="patient_signature" id="patient_signature">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Date Signed</label>
                        <input type="date" name="date_signed" id="date_signed" required
                               value="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex justify-between items-center">
                        <button type="button" onclick="window.location.reload()"
                                class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                            Reset Form
                        </button>
                        <button type="submit" id="submitBtn"
                                class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                            Register Patient
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        // Initialize Signature Pad
        const canvas = document.getElementById('signaturePad');
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });

        // Clear signature
        document.getElementById('clearSignature').addEventListener('click', function() {
            signaturePad.clear();
        });

        // Calculate age when DOB changes
        document.getElementById('date_of_birth').addEventListener('change', function() {
            const dob = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }

            document.getElementById('age').value = age >= 0 ? age : '';
        });

        // Patient search functionality
        let searchTimeout;
        document.getElementById('patientSearch').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const search = this.value.trim();

            if (search.length < 2) {
                document.getElementById('searchResults').classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(function() {
                fetch(`/walkin/search?search=${encodeURIComponent(search)}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(patients => {
                    const resultsDiv = document.getElementById('searchResults');

                    if (patients.length === 0) {
                        resultsDiv.innerHTML = '<div class="p-4 text-gray-500">No patients found</div>';
                    } else {
                        resultsDiv.innerHTML = patients.map(patient => `
                            <div class="p-3 hover:bg-blue-50 cursor-pointer border-b patient-result"
                                 data-patient='${JSON.stringify(patient)}'>
                                <div class="font-semibold">${patient.full_name}</div>
                                <div class="text-sm text-gray-600">
                                    ${patient.patient_number} | ${patient.mobile_number} | Age: ${patient.age}
                                </div>
                                <div class="text-xs text-gray-500">Last visit: ${patient.last_visit}</div>
                            </div>
                        `).join('');

                        // Add click handlers
                        document.querySelectorAll('.patient-result').forEach(el => {
                            el.addEventListener('click', function() {
                                const patient = JSON.parse(this.dataset.patient);
                                fillPatientData(patient);
                                document.getElementById('searchResults').classList.add('hidden');
                            });
                        });
                    }

                    resultsDiv.classList.remove('hidden');
                })
                .catch(error => console.error('Error:', error));
            }, 300);
        });

        // Fill form with patient data
        function fillPatientData(patient) {
            document.getElementById('patient_id').value = patient.id;
            document.getElementById('last_name').value = patient.last_name;
            document.getElementById('first_name').value = patient.first_name;
            document.getElementById('middle_name').value = patient.middle_name || '';
            document.getElementById('sex').value = patient.sex;
            document.getElementById('date_of_birth').value = patient.date_of_birth;
            document.getElementById('age').value = patient.age;
            document.getElementById('mobile_number').value = patient.mobile_number;
            document.getElementById('email_address').value = patient.email_address || '';
            document.getElementById('complete_address').value = patient.complete_address;
            document.getElementById('emergency_contact_name').value = patient.emergency_contact_name;
            document.getElementById('emergency_contact_relationship').value = patient.emergency_contact_relationship;
            document.getElementById('emergency_contact_number').value = patient.emergency_contact_number;

            // Set patient type to Returning
            document.getElementById('patient_type').value = 'Returning';

            // Disable personal info fields for returning patients
            ['last_name', 'first_name', 'middle_name', 'sex', 'date_of_birth'].forEach(field => {
                document.getElementById(field).readOnly = true;
                document.getElementById(field).classList.add('bg-gray-100');
            });
        }

        // Form submission
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate signature
            if (signaturePad.isEmpty()) {
                alert('Please provide your signature');
                return;
            }

            // Get signature data
            document.getElementById('patient_signature').value = signaturePad.toDataURL();

            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registering...';

            fetch('/walkin/register', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Registration Successful!\n\nPatient Number: ${data.data.patient_number}\nVisit Number: ${data.data.visit_number}\nPatient Name: ${data.data.patient_name}`);
                    window.location.href = `/walkin/confirmation/${data.data.visit_id}`;
                } else {
                    alert('Registration failed: ' + (data.message || 'Unknown error'));
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Register Patient';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during registration');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register Patient';
            });
        });

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#patientSearch') && !e.target.closest('#searchResults')) {
                document.getElementById('searchResults').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
