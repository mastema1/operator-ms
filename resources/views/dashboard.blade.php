<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Postes critiques EGR ICE1</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Critical Postes Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Occupied Critical Postes Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">Postes Critiques Occupé</h3>
                            <p class="text-3xl font-bold text-green-600">7</p>
                            <p class="text-sm text-gray-600">Critical positions currently occupied</p>
                        </div>
                    </div>
                </div>

                <!-- Non-Occupied Critical Postes Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">Postes Critiques Non-occupé</h3>
                            <p class="text-3xl font-bold text-red-600">0</p>
                            <p class="text-sm text-gray-600">Critical positions currently understaffed</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Critical Posts Summary Table -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Critical Posts Assignment</h3>
                
                {{-- TEMPORARY: Static test table for demonstration - REMOVE WHEN REVERTING --}}
                @if(true) {{-- Change to false to revert to dynamic table --}}
                    <div class="max-h-80 overflow-y-auto overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="p-3 text-sm font-semibold text-gray-700">Ligne</th>
                                    <th class="p-3 text-sm font-semibold text-gray-700">Poste Critique</th>
                                    <th class="p-3 text-sm font-semibold text-gray-700">Opérateur</th>
                                    <th class="p-3 text-sm font-semibold text-gray-700">Présence</th>
                                    <th class="p-3 text-sm font-semibold text-gray-700">Backup</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Bol
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Otman Miloudi</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <a href="{{ route('operators.index') }}" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            Backup list
                                        </a>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Bol

                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Othmane El Gamraoui</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <a href="{{ route('operators.index') }}" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            Backup list
                                        </a>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Bol
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Sohaib Bigaa</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <a href="{{ route('operators.index') }}" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            Backup list
                                        </a>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            ABS
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Khadija Chafik</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <a href="{{ route('operators.index') }}" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            Backup list
                                        </a>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Poste 23
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Mohssine El yakhlafi</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <a href="{{ route('operators.index') }}" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            Backup list
                                        </a>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            poste 24
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Omar Khafdi</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <a href="{{ route('operators.index') }}" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            Backup list
                                        </a>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        VISSEUSE
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Abdessamad Mourabou</td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800">
                                        <a href="{{ route('operators.index') }}" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            Backup list
                                        </a>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                {{-- ORIGINAL DYNAMIC TABLE - HIDDEN FOR TESTING --}}
                @elseif($criticalPostesWithOperators->count() > 0)
                    <div class="max-h-80 overflow-y-auto overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="p-3 text-sm font-semibold text-gray-700">Ligne</th>
                                    <th class="p-3 text-sm font-semibold text-gray-700">Poste Critique</th>
                                    <th class="p-3 text-sm font-semibold text-gray-700">Opérateur</th>
                                    <th class="p-3 text-sm font-semibold text-gray-700">Présence</th>
                                    <th class="p-3 text-sm font-semibold text-gray-700">Backup</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($criticalPostesWithOperators as $assignment)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="p-3 text-sm text-gray-800">{{ $assignment['ligne'] ?? '-' }}</td>
                                        <td class="p-3 text-sm text-gray-800">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ $assignment['poste_name'] }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-sm text-gray-800 font-medium">{{ $assignment['operator_name'] }}</td>
                                        <td class="p-3 text-sm text-gray-800">
                                            <div class="flex items-center">
                                                @if($assignment['is_present'])
                                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                                    <span class="text-green-700 font-medium">Présent</span>
                                                @else
                                                    <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                                    <span class="text-red-700 font-medium">Absent</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-3 text-sm text-gray-800">
                                            <a href="{{ route('operators.index') }}" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                                Backup list
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-2">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm">No operators assigned to critical posts</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Navigation Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Operator Management Card -->
                <a href="{{ route('operators.index') }}" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-6 border border-gray-200 hover:border-blue-300">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 text-center mb-2">Operator Management</h3>
                    <p class="text-sm text-gray-600 text-center">Manage operators, view details, and update information</p>
                </a>

                <!-- Absence Management Card -->
                <a href="{{ route('absences.index') }}" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-6 border border-gray-200 hover:border-green-300">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 text-center mb-2">Absence Management</h3>
                    <p class="text-sm text-gray-600 text-center">Track and manage operator absences and attendance</p>
                </a>

                <!-- Post Status Card -->
                <a href="{{ route('post-status.index') }}" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-6 border border-gray-200 hover:border-purple-300">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 text-center mb-2">Post Status</h3>
                    <p class="text-sm text-gray-600 text-center">View current status and assignments of all posts</p>
                </a>

                <!-- Postes Management Card -->
                <a href="{{ route('postes.index') }}" class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-6 border border-gray-200 hover:border-orange-300">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 text-center mb-2">Postes Management</h3>
                    <p class="text-sm text-gray-600 text-center">Manage work positions and their critical status</p>
                </a>
            </div>
        </div>
    </div> --}}
</x-app-layout>
