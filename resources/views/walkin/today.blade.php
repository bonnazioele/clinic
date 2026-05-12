<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Registrations - CliniQ</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Today's Walk-In Registrations</h1>
                        <p class="text-gray-600 mt-1">{{ date('F d, Y') }}</p>
                    </div>
                    <a href="/walkin" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        + New Registration
                    </a>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <p class="text-sm text-gray-600">Total Registrations</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $registrations->count() }}</p>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <p class="text-sm text-gray-600">Emergency Cases</p>
                    <p class="text-3xl font-bold text-red-600">{{ $registrations->where('priority_level', 'Emergency')->count() }}</p>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <p class="text-sm text-gray-600">Urgent Cases</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $registrations->where('priority_level', 'Urgent')->count() }}</p>
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <p class="text-sm text-gray-600">New Patients</p>
                    <p class="text-3xl font-bold text-green-600">{{ $registrations->where('patient_type', 'New')->count() }}</p>
                </div>
            </div>

            <!-- Registrations Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Visit Number
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Patient Info
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Time In
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Service
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Priority
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($registrations as $registration)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $registration->visit_number }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $registration->patient->patient_number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $registration->patient->full_name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $registration->patient->sex }} | {{ $registration->patient->age }} yrs old
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $registration->patient->mobile_number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $registration->time_in->format('h:i A') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $registration->requested_service }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($registration->priority_level === 'Emergency') bg-red-100 text-red-800
                                        @elseif($registration->priority_level === 'Urgent') bg-orange-100 text-orange-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ $registration->priority_level }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($registration->patient_type === 'New') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $registration->patient_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($registration->status === 'Registered') bg-blue-100 text-blue-800
                                        @elseif($registration->status === 'In Progress') bg-yellow-100 text-yellow-800
                                        @elseif($registration->status === 'Completed') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $registration->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('walkin.confirmation', $registration->id) }}"
                                       class="text-blue-600 hover:text-blue-900 mr-3">
                                        View
                                    </a>
                                    <a href="{{ route('walkin.print', $registration->id) }}"
                                       class="text-green-600 hover:text-green-900">
                                        Print
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    No registrations for today
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
