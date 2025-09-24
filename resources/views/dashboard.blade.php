<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
            <h1 id="dashboard-title" 
                class="font-semibold text-xl text-gray-800 leading-tight inline-editable-title" 
                data-inline-edit="title"
                data-api-url="{{ route('api.dashboard.title.update') }}"
                data-max-length="255"
                data-min-length="1"
                data-placeholder="Enter dashboard title..."
                data-show-edit-icon="true"
                title="Click to edit dashboard title">{{ $dashboardTitle }}</h1>
        </div>
    </x-slot>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Inline Edit Title JavaScript -->
    <script src="{{ asset('js/inline-edit-title.js') }}"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Critical Postes Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Occupied Critical Postes Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">Postes Critiques Occupé</h3>
                            <p class="text-sm text-gray-600">Critical positions currently occupied</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @if(isset($ligneBreakdown) && $ligneBreakdown->count() > 0)
                            @foreach($ligneBreakdown as $ligne => $counts)
                                <div class="flex justify-between items-center py-2 px-3 bg-green-50 rounded-md">
                                    <span class="text-sm font-medium text-gray-700">{{ $ligne }}</span>
                                    <span class="text-lg font-bold text-green-600">{{ $counts['occupied'] }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-md">
                                <span class="text-sm text-gray-500">No critical positions found</span>
                                <span class="text-lg font-bold text-gray-400">0</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Non-Occupied Critical Postes Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-800">Postes Critiques Non-occupé</h3>
                            <p class="text-sm text-gray-600">Critical positions currently understaffed</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @if(isset($ligneBreakdown) && $ligneBreakdown->count() > 0)
                            @foreach($ligneBreakdown as $ligne => $counts)
                                <div class="flex justify-between items-center py-2 px-3 {{ $counts['non_occupied'] > 0 ? 'bg-red-50' : 'bg-gray-50' }} rounded-md">
                                    <span class="text-sm font-medium text-gray-700">{{ $ligne }}</span>
                                    <span class="text-lg font-bold {{ $counts['non_occupied'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $counts['non_occupied'] }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-md">
                                <span class="text-sm text-gray-500">No critical positions found</span>
                                <span class="text-lg font-bold text-gray-400">0</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Critical Posts Summary Table -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Critical Posts Assignment</h3>
                
                {{-- TEMPORARY: Static test table for demonstration - REMOVED --}}
                @if(false) {{-- Disabled static table --}}
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
                                    <td class="p-3 text-sm text-gray-800 relative">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-gray-800">
                                            Bol
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Otman Miloudi</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <button onclick="toggleBackupPopover(this)" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            View Backups
                                        </button>
                                        <div class="backup-popover hidden absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg">
                                            <div class="p-3">
                                                <h4 class="text-sm font-medium text-gray-900 mb-2">Backup Operators</h4>
                                                <ul class="text-sm text-gray-700 space-y-1">
                                                    <li>• Ahmed Benali</li>
                                                    <li>• Fatima Cherkaoui</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800 relative">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-gray-800">
                                        Bol

                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Othmane El Gamraoui</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <button onclick="toggleBackupPopover(this)" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            View Backups
                                        </button>
                                        <div class="backup-popover hidden absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg">
                                            <div class="p-3">
                                                <h4 class="text-sm font-medium text-gray-900 mb-2">Backup Operators</h4>
                                                <ul class="text-sm text-gray-700 space-y-1">
                                                    <li>• Hicham Benjelloun</li>
                                                    <li>• Zineb Berrada</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800 relative">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-gray-800">
                                            Bol
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Sohaib Bigaa</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <button onclick="toggleBackupPopover(this)" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            View Backups
                                        </button>
                                        <div class="backup-popover hidden absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg">
                                            <div class="p-3">
                                                <h4 class="text-sm font-medium text-gray-900 mb-2">Backup Operators</h4>
                                                <ul class="text-sm text-gray-700 space-y-1">
                                                    <li>• Larbi Bigaa</li>
                                                    <li>• Noureddine Chakir</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800 relative">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-gray-800">
                                            ABS
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Khadija Chafik</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <button onclick="toggleBackupPopover(this)" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            View Backups
                                        </button>
                                        <div class="backup-popover hidden absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg">
                                            <div class="p-3">
                                                <h4 class="text-sm font-medium text-gray-900 mb-2">Backup Operators</h4>
                                                <ul class="text-sm text-gray-700 space-y-1">
                                                    <li>• Mehdi Cherkaoui</li>
                                                    <li>• Soufiane Chraibi</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800 relative">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-gray-800">
                                            Poste 23
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Mohssine El yakhlafi</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <button onclick="toggleBackupPopover(this)" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            View Backups
                                        </button>
                                        <div class="backup-popover hidden absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg">
                                            <div class="p-3">
                                                <h4 class="text-sm font-medium text-gray-900 mb-2">Backup Operators</h4>
                                                <ul class="text-sm text-gray-700 space-y-1">
                                                    <li>• Jamal Douiri</li>
                                                    <li>• Mohamed Drissi</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800 relative">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-gray-800">
                                            poste 24
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Omar Khafdi</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <button onclick="toggleBackupPopover(this)" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            View Backups
                                        </button>
                                        <div class="backup-popover hidden absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg">
                                            <div class="p-3">
                                                <h4 class="text-sm font-medium text-gray-900 mb-2">Backup Operators</h4>
                                                <ul class="text-sm text-gray-700 space-y-1">
                                                    <li>• Abdellah howari</li>
                                                    <li>• Amine fakir</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-800 relative">ligne 1</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-gray-800">
                                        VISSEUSE
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 font-medium">Abdessamad Mourabou</td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm text-gray-800 relative">
                                        <button onclick="toggleBackupPopover(this)" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                            View Backups
                                        </button>
                                        <div class="backup-popover hidden absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg">
                                            <div class="p-3">
                                                <h4 class="text-sm font-medium text-gray-900 mb-2">Backup Operators</h4>
                                                <ul class="text-sm text-gray-700 space-y-1">
                                                    <li>• Khalid sbaibi</li>
                                                    <li>• Rajae El Fassi</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                {{-- DYNAMIC TABLE WITH STATUS TAGS --}}
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
                                {{-- DEBUG: Show assignment data --}}
                                @if(app()->environment('local'))
                                    <!-- DEBUG: Total assignments: {{ $criticalPostesWithOperators->count() }} -->
                                    @foreach($criticalPostesWithOperators as $index => $assignment)
                                        @if(isset($assignment['occupation_type']) && $assignment['occupation_type'] === 'backup')
                                            <!-- DEBUG: Backup assignment found at index {{ $index }}: {{ $assignment['poste_name'] }} on {{ $assignment['ligne'] }} -->
                                        @endif
                                    @endforeach
                                @endif
                                @foreach($criticalPostesWithOperators as $assignment)
                                    <tr class="border-b border-gray-100 {{ 
                                        $assignment['is_non_occupe'] ? 'bg-red-50 border-red-300 hover:bg-red-100' : 
                                        (isset($assignment['urgency_level']) && $assignment['urgency_level'] === 3 ? 'bg-red-50 border-red-200 hover:bg-red-100' :
                                        (isset($assignment['urgency_level']) && $assignment['urgency_level'] === 2 ? 'bg-yellow-50 border-yellow-200 hover:bg-yellow-100' : 
                                        'hover:bg-gray-50'))
                                    }}">
                                        <td class="p-3 text-sm text-gray-800 relative">
                                            @if($assignment['is_non_occupe'])
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></div>
                                                    {{ $assignment['ligne'] ?? 'Ligne1' }}
                                                </div>
                                            @else
                                                {{ $assignment['ligne'] ?? 'Ligne1' }}
                                            @endif
                                        </td>
                                        <td class="p-3 text-sm text-gray-800 relative">
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $assignment['is_non_occupe'] ? 'bg-red-200 text-gray-800 border border-red-300' : 'bg-red-100 text-gray-800' }}">
                                                    {{ $assignment['poste_name'] }}
                                                </span>
                                                
                                                {{-- Dynamic Status Tags - Only show when tag exists --}}
                                                @if(isset($assignment['status_tag']) && !empty($assignment['status_tag']) && isset($assignment['status_class']))
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold {{ $assignment['status_class'] }}">
                                                        {{ $assignment['status_tag'] }}
                                                        @if($assignment['status_tag'] === 'URGENT')
                                                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        @elseif($assignment['status_tag'] === 'Occupied')
                                                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        @endif
                                                    </span>
                                                @endif
                                                
                                            </div>
                                        </td>
                                        <td class="p-3 text-sm text-gray-800 font-medium">
                                            @if(isset($assignment['occupation_type']) && $assignment['occupation_type'] === 'backup')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $assignment['operator_name'] }}
                                                </span>
                                            @else
                                                {{ $assignment['operator_name'] }}
                                            @endif
                                        </td>
                                        <td class="p-3 text-sm text-gray-800 relative">
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
                                        <td class="p-3 text-sm text-gray-800 relative">
                                            <div class="backup-assignment-container" data-poste-id="{{ $assignment['poste_id'] }}" data-operator-id="{{ $assignment['operator_id'] ?? '' }}">
                                                @if(count($assignment['backup_assignments']) == 0)
                                                    <!-- State 1: No backups assigned -->
                                                    <button onclick="openBackupAssignment(this, {{ $loop->index }})" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                                        Assign Backup
                                                    </button>
@else
                                                    <!-- State 2: One backup assigned -->
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($assignment['backup_assignments'] as $backup)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                {{ $backup['operator_name'] }}
                                                                <button onclick="removeBackup({{ $backup['id'] }})" class="ml-1 text-green-600 hover:text-green-800">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                    </svg>
                                                                </button>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Primary Backup Popover -->
                                            <div id="backup-popover-{{ $loop->index }}" class="backup-popover hidden absolute z-50 mt-2 w-64 bg-white border border-gray-200 rounded-md shadow-xl" style="background-color: white;">
                                                <div class="p-4 bg-white">
                                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Assign Backup Operator</h4>
                                                    <div class="space-y-2">
                                                        <div onclick="openOperatorSelection({{ $loop->index }}, 1)" class="backup-slot cursor-pointer p-2 border border-gray-300 rounded-md hover:bg-gray-50 text-sm text-gray-600 bg-white" data-slot="1">
                                                            Select Backup Operator
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Operator Selection Panel -->
                                            <div id="operator-panel-{{ $loop->index }}" class="operator-panel hidden fixed w-80 bg-white border border-gray-200 rounded-md shadow-xl" style="background-color: white; z-index: 10001; pointer-events: auto;">
                                                <div class="p-4 bg-white" style="pointer-events: auto;">
                                                    <div class="flex justify-between items-center mb-3">
                                                        <h4 class="text-sm font-medium text-gray-900">Select Backup Operator</h4>
                                                        <button onclick="closeOperatorPanel({{ $loop->index }})" class="text-gray-400 hover:text-gray-600">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="mb-3">
                                                        <input type="text" id="operator-search-{{ $loop->index }}" placeholder="Search operators..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" onkeyup="filterOperators({{ $loop->index }})" style="pointer-events: auto;">
                                                    </div>
                                                    <div class="max-h-48 overflow-y-auto bg-white" style="pointer-events: auto;">
                                                        <div id="operator-list-{{ $loop->index }}" class="operator-list space-y-1 bg-white" style="pointer-events: auto;">
                                                            <!-- Operators will be loaded here -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

    <script>
        let operators = [];
        let currentRowIndex = null;
        let currentSlot = null;

        // Load operators when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Debug: Check if buttons exist
            const assignButtons = document.querySelectorAll('button[onclick*="openBackupAssignment"]');
            console.log('Found assign backup buttons:', assignButtons.length);
            
            // Debug: Check if popovers exist
            const popovers = document.querySelectorAll('[id^="backup-popover-"]');
            console.log('Found backup popovers:', popovers.length);
            
            operators = [];
        });

        function openBackupAssignment(button, rowIndex) {
            console.log('openBackupAssignment called with rowIndex:', rowIndex);
            
            // Close all other popovers
            document.querySelectorAll('.backup-popover, .operator-panel').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Toggle current popover
            const popover = document.getElementById(`backup-popover-${rowIndex}`);
            console.log('Popover element:', popover);
            
            if (popover) {
                const isHidden = popover.classList.contains('hidden');
                if (isHidden) {
                    popover.classList.remove('hidden');
                } else {
                    popover.classList.add('hidden');
                }
                console.log('Popover visibility changed, now hidden:', popover.classList.contains('hidden'));
            } else {
                console.error('Popover not found for rowIndex:', rowIndex);
                // Debug: List all backup popovers
                const allPopovers = document.querySelectorAll('[id^="backup-popover-"]');
                console.log('Available popovers:', Array.from(allPopovers).map(p => p.id));
            }
        }

        function openOperatorSelection(rowIndex, slot) {
            console.log('openOperatorSelection called with rowIndex:', rowIndex, 'slot:', slot);
            currentRowIndex = rowIndex;
            currentSlot = slot;
            
            // Hide all panels first
            document.querySelectorAll('.operator-panel').forEach(panel => {
                panel.classList.add('hidden');
            });
            
            // Get the backup popover position
            const backupPopover = document.getElementById(`backup-popover-${rowIndex}`);
            const popoverRect = backupPopover.getBoundingClientRect();
            
            // Show operator selection panel
            const panel = document.getElementById(`operator-panel-${rowIndex}`);
            console.log('Panel element:', panel);
            
            if (!panel) {
                console.error('Operator panel not found for rowIndex:', rowIndex);
                return;
            }
            
            // Position the panel to the right of the popover
            panel.style.left = (popoverRect.right + 10) + 'px';
            panel.style.top = popoverRect.top + 'px';
            panel.style.zIndex = '10001';
            panel.style.pointerEvents = 'auto';
            
            panel.classList.remove('hidden');
            console.log('Panel shown, z-index set to:', panel.style.zIndex);
            
            // Load operators into the list
            loadOperators(rowIndex);
            
            // Clear search and focus on it
            const searchInput = document.getElementById(`operator-search-${rowIndex}`);
            if (searchInput) {
                searchInput.value = '';
                searchInput.style.pointerEvents = 'auto';
                setTimeout(() => {
                    searchInput.focus();
                    console.log('Search input focused');
                }, 100);
            }
        }

        function closeOperatorPanel(rowIndex) {
            console.log('closeOperatorPanel called for rowIndex:', rowIndex);
            const panel = document.getElementById(`operator-panel-${rowIndex}`);
            if (panel) {
                panel.classList.add('hidden');
            }
        }

        function loadOperators(rowIndex) {
            console.log('loadOperators called for rowIndex:', rowIndex);
            const operatorList = document.getElementById(`operator-list-${rowIndex}`);
            
            if (!operatorList) {
                console.error('Operator list element not found for rowIndex:', rowIndex);
                return;
            }
            
            operatorList.innerHTML = '<div class="text-center py-2 text-gray-500">Loading...</div>';
            
            // Get operator ID from the current row's backup assignment container
            const row = document.querySelector(`#backup-popover-${rowIndex}`).closest('tr');
            const container = row.querySelector('[data-operator-id]');
            const operatorId = container ? container.dataset.operatorId : null;
            
            if (!operatorId) {
                console.error('Could not find operator ID for row:', rowIndex);
                operatorList.innerHTML = '<div class="text-center py-2 text-red-500">Error: No operator ID found</div>';
                return;
            }
            
            // Fetch available operators from API
            fetch(`/api/backup-assignments/available-operators?operator_id=${operatorId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Operators loaded:', data.length);
                operatorList.innerHTML = '';
                operators = data; // Update global operators array
                
                if (data.length === 0) {
                    operatorList.innerHTML = '<div class="text-center py-2 text-gray-500">No operators available</div>';
                    return;
                }
                
                data.forEach((operator, index) => {
                    const operatorDiv = document.createElement('div');
                    operatorDiv.className = 'operator-item cursor-pointer p-2 hover:bg-blue-50 rounded text-sm border-b border-gray-100';
                    operatorDiv.textContent = `${operator.first_name} ${operator.last_name}`;
                    operatorDiv.style.pointerEvents = 'auto';
                    operatorDiv.style.userSelect = 'none';
                    operatorDiv.dataset.operatorId = operator.id;
                    operatorDiv.dataset.operatorName = `${operator.first_name} ${operator.last_name}`;
                    
                    // Use onclick attribute for better compatibility
                    operatorDiv.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Operator clicked:', operator.first_name, operator.last_name, 'for slot:', currentSlot);
                        selectOperator(operator, rowIndex, currentSlot);
                    };
                    
                    operatorList.appendChild(operatorDiv);
                    console.log('Added operator:', operator.first_name, operator.last_name);
                });
            })
            .catch(error => {
                console.error('Error loading operators:', error);
                operatorList.innerHTML = '<div class="text-center py-2 text-red-500">Error loading operators</div>';
            });
        }

        function filterOperators(rowIndex) {
            const searchTerm = document.getElementById(`operator-search-${rowIndex}`).value.toLowerCase();
            const operatorItems = document.querySelectorAll(`#operator-list-${rowIndex} .operator-item`);
            
            operatorItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function selectOperator(operator, rowIndex, slot) {
            console.log('selectOperator called:', {operator, rowIndex, slot});
            
            // Get both poste ID and operator ID from the current row's backup assignment container
            const row = document.querySelector(`#backup-popover-${rowIndex}`).closest('tr');
            const container = row.querySelector('[data-poste-id]');
            const posteId = container ? container.dataset.posteId : null;
            const operatorId = container ? container.dataset.operatorId : null;
            
            console.log('Found posteId:', posteId, 'operatorId:', operatorId);
            console.log('Request payload:', {
                poste_id: posteId,
                operator_id: operatorId,
                backup_operator_id: operator.id,
                backup_slot: slot,
                assigned_date: new Date().toISOString().split('T')[0]
            });
            
            if (!posteId || !operatorId) {
                alert('Error: Could not find poste ID or operator ID');
                return;
            }
            
            // Send assignment request to backend
            fetch('/api/backup-assignments/assign', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    poste_id: posteId,
                    operator_id: operatorId,
                    backup_operator_id: operator.id,
                    backup_slot: slot,
                    assigned_date: new Date().toISOString().split('T')[0]
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Parsed response data:', data);
                if (data.success) {
                    // Update the backup slot text in the popover (simplified for single slot)
                    const backupSlot = document.querySelector(`#backup-popover-${rowIndex} .backup-slot`);
                    if (backupSlot) {
                        backupSlot.textContent = `${operator.first_name} ${operator.last_name}`;
                        backupSlot.classList.remove('text-gray-600');
                        backupSlot.classList.add('text-green-600', 'font-medium');
                    }
                    
                    // Update the main backup assignment container dynamically
                    updateBackupAssignmentUI(rowIndex, data.assignment, slot);
                    
                    // Hide panels
                    document.querySelectorAll('.operator-panel, .backup-popover').forEach(panel => {
                        panel.classList.add('hidden');
                    });
                    
                    console.log('Backup assignment updated successfully without page reload');
                } else {
                    console.error('API returned error:', data);
                    alert('Error assigning backup: ' + (data.message || data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error assigning backup:', error);
                alert('Error assigning backup: ' + error.message);
            });
        }

        // Function to update backup assignment UI dynamically
        function updateBackupAssignmentUI(rowIndex, assignment, slot) {
            console.log('updateBackupAssignmentUI called:', {rowIndex, assignment, slot});
            
            // Get the backup assignment container for this row
            const row = document.querySelector(`#backup-popover-${rowIndex}`).closest('tr');
            const container = row.querySelector('.backup-assignment-container');
            
            if (!container) {
                console.error('Backup assignment container not found');
                return;
            }
            
            // Fetch current assignment for this operator to rebuild the UI
            const operatorId = container.dataset.operatorId;
            fetch(`/api/backup-assignments/operator/${operatorId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Convert single assignment to array format for consistency
                    const assignments = data.assignment ? [data.assignment] : [];
                    rebuildBackupAssignmentContainer(container, assignments, rowIndex);
                    // Also update the side panel backup slots
                    updateBackupSidePanel(rowIndex, assignments);
                }
            })
            .catch(error => {
                console.error('Error fetching current assignments:', error);
                // Fallback: reload page if dynamic update fails
                window.location.reload();
            });
        }

        // Function to rebuild the backup assignment container HTML
        function rebuildBackupAssignmentContainer(container, assignments, rowIndex) {
            console.log('rebuildBackupAssignmentContainer called:', {assignments, rowIndex});
            
            let html = '';
            const assignmentCount = assignments.length;
            
            if (assignmentCount === 0) {
                // State 1: No backups assigned
                html = `
                    <button onclick="openBackupAssignment(this, ${rowIndex})" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                        Assign Backup
                    </button>
                `;
            } else {
                // State 2: One backup assigned (simplified - no second backup option)
                html = `
                    <div class="flex flex-wrap gap-1">
                        ${assignments.map(backup => `
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ${backup.operator_name}
                                <button onclick="removeBackup(${backup.id})" class="ml-1 text-green-600 hover:text-green-800">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        `).join('')}
                    </div>
                `;
            }
            
            container.innerHTML = html;
            console.log('Backup assignment container updated with', assignmentCount, 'assignments');
        }

        // Function to update the side panel backup slot (simplified for single backup)
        function updateBackupSidePanel(rowIndex, assignments) {
            console.log('updateBackupSidePanel called:', {rowIndex, assignments});
            
            const backupSlot = document.querySelector(`#backup-popover-${rowIndex} .backup-slot`);
            
            if (!backupSlot) {
                console.error('Backup slot not found for rowIndex:', rowIndex);
                return;
            }
            
            // Reset slot to default state
            backupSlot.textContent = 'Select Backup Operator';
            backupSlot.classList.remove('text-green-600', 'font-medium');
            backupSlot.classList.add('text-gray-600');
            
            // Update slot with current assignment (only one now)
            if (assignments.length > 0) {
                const assignment = assignments[0]; // Only take the first assignment
                backupSlot.textContent = assignment.operator_name;
                backupSlot.classList.remove('text-gray-600');
                backupSlot.classList.add('text-green-600', 'font-medium');
            }
            
            console.log('Side panel updated with', assignments.length, 'assignment(s)');
        }

        // Helper function to update backup side panel
        function updateBackupSidePanel(rowIndex, assignments) {
            const sidePanel = document.querySelector(`#backup-side-panel-${rowIndex}`);
            if (sidePanel) {
                if (assignments.length === 0) {
                    sidePanel.innerHTML = '<p class="text-gray-500 text-sm">No backup assignments</p>';
                } else {
                    sidePanel.innerHTML = assignments.map(backup => `
                        <div class="flex items-center justify-between py-1">
                            <span class="text-sm">${backup.operator_name}</span>
                            <button onclick="removeBackup(${backup.id})" class="text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    `).join('');
                }
            }
        }

        // Function to remove backup assignment
        function removeBackup(backupId) {
            if (!confirm('Are you sure you want to remove this backup assignment?')) {
                return;
            }
            
            // Find the specific backup element and its container using the backup ID
            const backupElement = event.target.closest('span');
            const container = backupElement.closest('.backup-assignment-container');
            const operatorId = container.dataset.operatorId;
            
            // Find the correct row index by matching the operator ID (more reliable than poste ID)
            let rowIndex = null;
            document.querySelectorAll('.backup-assignment-container').forEach((containerEl, index) => {
                if (containerEl.dataset.operatorId === operatorId) {
                    rowIndex = index;
                }
            });
            
            fetch(`/api/backup-assignments/remove/${backupId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Method 1: Direct UI update using operator ID (most reliable)
                    if (operatorId) {
                        // Remove the backup pill directly from the container
                        backupElement.remove();
                        
                        // Update the container to show "Assign Backup" button
                        container.innerHTML = `
                            <button onclick="openBackupAssignment(this, ${rowIndex})" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                Assign Backup
                            </button>
                        `;
                        
                        // Also update the side panel if it exists
                        if (rowIndex !== null) {
                            updateBackupSidePanel(rowIndex, []);
                        }
                        
                        console.log('Backup assignment removed successfully with direct UI update');
                    } else if (rowIndex !== null) {
                        // Method 2: Fallback to the original method if operator ID is missing
                        updateBackupAssignmentUI(rowIndex, null, null);
                        console.log('Backup assignment removed successfully with fallback method');
                    } else {
                        // Method 3: Last resort - reload page
                        console.log('Could not determine row index, reloading page');
                        window.location.reload();
                    }
                } else {
                    alert('Error removing backup: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error removing backup:', error);
                alert('Error removing backup assignment');
            });
        }

        // Close popovers when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('button[onclick*="openBackupAssignment"]') && 
                !event.target.closest('.backup-popover') && 
                !event.target.closest('.operator-panel')) {
                document.querySelectorAll('.backup-popover, .operator-panel').forEach(el => {
                    el.classList.add('hidden');
                });
            }
        });

        // Legacy function for static table
        function toggleBackupPopover(button) {
            const popover = button.nextElementSibling;
            document.querySelectorAll('.backup-popover').forEach(p => {
                if (p !== popover) p.classList.add('hidden');
            });
            popover.classList.toggle('hidden');
        }
    </script>
</x-app-layout>
