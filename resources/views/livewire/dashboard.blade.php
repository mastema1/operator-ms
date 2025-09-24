<div class="p-6 space-y-6">
    <!-- Dashboard Header with Inline Edit Title -->
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

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Inline Edit Title JavaScript -->
    <script src="{{ asset('js/inline-edit-title.js') }}"></script>

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

    <!-- Search and Filter Controls (NEW FEATURE) -->
    <div class="flex justify-center">
        <div class="flex flex-col md:flex-row gap-4 w-full max-w-4xl">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or poste" class="w-full md:w-1/3 border rounded px-3 py-2" />
            <select wire:model.live="ligneFilter" class="w-full md:w-1/4 border rounded px-3 py-2">
                <option value="">All Lignes</option>
                @foreach($lignes as $ligne)
                    <option value="{{ $ligne }}">{{ $ligne }}</option>
                @endforeach
            </select>
            <button wire:click="refreshData" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors duration-200">Refresh</button>
        </div>
    </div>

    <!-- Critical Posts Summary Table -->
    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Critical Posts Assignment</h3>
        
        @if($criticalPostesWithOperators->count() > 0)
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
                        @foreach($criticalPostesWithOperators as $index => $assignment)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="p-3 text-sm text-gray-800 relative">
                                    @if(isset($assignment['ligne']))
                                        {{ $assignment['ligne'] }}
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
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold opacity-decay-preserve status-tag {{ $assignment['status_class'] }}">
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
                                            Backup: {{ $assignment['operator_name'] }}
                                        </span>
                                    @else
                                        {{ $assignment['operator_name'] }}
                                    @endif
                                </td>
                                <td class="p-3 text-sm text-gray-800 relative">
                                    @if(isset($assignment['occupation_type']) && $assignment['occupation_type'] === 'backup')
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                            <span class="text-blue-700 font-medium">Backup</span>
                                        </div>
                                    @elseif($assignment['is_present'])
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-green-700 font-medium">Présent</span>
                                        </div>
                                    @else
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                            <span class="text-red-700 font-medium">Absent</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-3 text-sm text-gray-800 relative">
                                    <div class="backup-assignment-container" data-poste-id="{{ $assignment['poste_id'] }}" data-operator-id="{{ $assignment['operator_id'] ?? '' }}">
                                        @if(count($assignment['backup_assignments']) == 0)
                                            <!-- State 1: No backups assigned -->
                                            <button onclick="openBackupAssignment(this, {{ $loop->index }})" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 hover:text-blue-800 rounded-md text-xs font-medium transition-colors duration-200">
                                                Assign Backup
                                            </button>
                                        @else
                                            <!-- State 2: Backups assigned -->
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
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-gray-500 text-lg mb-2">No critical positions found</div>
                <div class="text-gray-400 text-sm">
                    @if(!empty($search) || !empty($ligneFilter))
                        Try adjusting your search or filter criteria.
                    @else
                        No critical positions are currently configured for your tenant.
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Backdrop -->
    <div id="modal-backdrop" class="hidden fixed inset-0 bg-black bg-opacity-25 z-40" onclick="closeAllModals()"></div>

    <!-- Backup Assignment Modals (Outside table to prevent layout interference) -->
    @if($criticalPostesWithOperators->count() > 0)
        @foreach($criticalPostesWithOperators as $assignment)
            <!-- Backup Assignment Popover -->
            <div id="backup-popover-{{ $loop->index }}" class="backup-popover hidden absolute z-50 w-64 bg-white border-2 border-blue-200 rounded-lg shadow-2xl">
                <div class="p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Assign Backup Operators
                    </h4>
                    <div class="space-y-2">
                        <div class="backup-slot cursor-pointer p-3 border border-blue-200 rounded-md text-sm text-blue-700 hover:bg-blue-50 hover:border-blue-300 transition-colors duration-200" onclick="openOperatorSelection({{ $loop->index }}, 1, '{{ $assignment['operator_id'] ?? '' }}', '{{ $assignment['poste_id'] ?? '' }}')">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Select Backup Operator
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operator Selection Panel -->
            <div id="operator-panel-{{ $loop->index }}" class="operator-panel hidden absolute z-50 w-80 bg-white border-2 border-green-200 rounded-lg shadow-2xl">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-gray-900">Select Operator</h4>
                        <button onclick="closeOperatorPanel({{ $loop->index }})" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <input type="text" id="operator-search-{{ $loop->index }}" placeholder="Search operators..." class="w-full p-2 border border-gray-200 rounded text-sm mb-3" oninput="filterOperators({{ $loop->index }})">
                    <div id="operator-list-{{ $loop->index }}" class="max-h-48 overflow-y-auto">
                        <!-- Operators will be loaded here dynamically -->
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

{{-- JavaScript for backup assignment functionality --}}
<script>
    let operators = [];
    let currentRowIndex = null;
    let currentSlot = null;
    let currentOperatorId = null;
    let currentPosteId = null;

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
                // Show backdrop
                const backdrop = document.getElementById('modal-backdrop');
                if (backdrop) {
                    backdrop.classList.remove('hidden');
                }
                
                // Position the popover directly next to the button that was clicked
                // For absolute positioning, we need to account for scroll position
                const buttonRect = button.getBoundingClientRect();
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                
                const popoverWidth = 256; // w-64 = 16rem = 256px
                
                // Try to position to the right of the button first
                let leftPosition = buttonRect.right + scrollLeft + 8; // 8px gap
                let topPosition = buttonRect.top + scrollTop;
                
                // If popup would go off-screen to the right, position it to the left
                if (leftPosition + popoverWidth > window.innerWidth + scrollLeft) {
                    leftPosition = buttonRect.left + scrollLeft - popoverWidth - 8;
                }
                
                // If still off-screen to the left, position below the button instead
                if (leftPosition < scrollLeft + 8) {
                    leftPosition = buttonRect.left + scrollLeft;
                    topPosition = buttonRect.bottom + scrollTop + 8;
                }
                
                // Ensure popup doesn't go off the top of the screen
                if (topPosition < scrollTop + 8) {
                    topPosition = scrollTop + 8;
                }
                
                popover.style.left = leftPosition + 'px';
                popover.style.top = topPosition + 'px';
                
                popover.classList.remove('hidden');
            } else {
                popover.classList.add('hidden');
                // Hide backdrop if no modals are open
                hideBackdropIfNoModals();
            }
            console.log('Popover visibility changed, now hidden:', popover.classList.contains('hidden'));
        } else {
            console.error('Popover not found for rowIndex:', rowIndex);
            // Debug: List all backup popovers
            const allPopovers = document.querySelectorAll('[id^="backup-popover-"]');
            console.log('Available popovers:', Array.from(allPopovers).map(p => p.id));
        }
    }

    function openOperatorSelection(rowIndex, slot, operatorId, posteId) {
        console.log('openOperatorSelection called with rowIndex:', rowIndex, 'slot:', slot, 'operatorId:', operatorId, 'posteId:', posteId);
        currentRowIndex = rowIndex;
        currentSlot = slot;
        currentOperatorId = operatorId;
        currentPosteId = posteId;
        
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
        
        // Position the panel directly next to the popover with smart positioning
        // For absolute positioning, we need to account for scroll position
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        
        const panelWidth = 320; // w-80 = 20rem = 320px
        const gap = 8; // 8px gap between popover and panel
        
        // Try to position to the right of the popover first
        let leftPosition = popoverRect.right + scrollLeft + gap;
        let topPosition = popoverRect.top + scrollTop;
        
        // If panel would go off-screen to the right, position it to the left of the popover
        if (leftPosition + panelWidth > window.innerWidth + scrollLeft) {
            leftPosition = popoverRect.left + scrollLeft - panelWidth - gap;
        }
        
        // If still off-screen to the left, position it below the popover instead
        if (leftPosition < scrollLeft + gap) {
            leftPosition = popoverRect.left + scrollLeft;
            topPosition = popoverRect.bottom + scrollTop + gap;
        }
        
        // Ensure panel doesn't go off the top of the screen
        if (topPosition < scrollTop + gap) {
            topPosition = scrollTop + gap;
        }
        
        // Ensure panel doesn't go off the bottom of the screen
        const panelHeight = 300; // Approximate height
        if (topPosition + panelHeight > window.innerHeight + scrollTop) {
            topPosition = window.innerHeight + scrollTop - panelHeight - gap;
        }
        
        panel.style.left = leftPosition + 'px';
        panel.style.top = topPosition + 'px';
        
        panel.classList.remove('hidden');
        console.log('Panel shown at position:', leftPosition, popoverRect.top);
        
        // Load operators into the list
        loadOperators(rowIndex, operatorId);
        
        // Clear search and focus on it
        const searchInput = document.getElementById(`operator-search-${rowIndex}`);
        if (searchInput) {
            searchInput.value = '';
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
            console.log('Panel hidden');
        }
        // Hide backdrop if no modals are open
        hideBackdropIfNoModals();
    }

    function closeAllModals() {
        console.log('closeAllModals called');
        // Hide all popovers and panels
        document.querySelectorAll('.backup-popover, .operator-panel').forEach(el => {
            el.classList.add('hidden');
        });
        
        // Hide backdrop
        const backdrop = document.getElementById('modal-backdrop');
        if (backdrop) {
            backdrop.classList.add('hidden');
        }
    }

    function hideBackdropIfNoModals() {
        // Check if any modals are still visible
        const visibleModals = document.querySelectorAll('.backup-popover:not(.hidden), .operator-panel:not(.hidden)');
        if (visibleModals.length === 0) {
            const backdrop = document.getElementById('modal-backdrop');
            if (backdrop) {
                backdrop.classList.add('hidden');
            }
        }
    }

    function loadOperators(rowIndex, operatorId) {
        console.log('loadOperators called for rowIndex:', rowIndex, 'operatorId:', operatorId);
        const operatorList = document.getElementById(`operator-list-${rowIndex}`);
        
        if (!operatorList) {
            console.error('Operator list element not found for rowIndex:', rowIndex);
            return;
        }
        
        operatorList.innerHTML = '<div class="text-center py-2 text-gray-500">Loading...</div>';
        
        if (!operatorId) {
            console.error('No operator ID provided for row:', rowIndex);
            operatorList.innerHTML = '<div class="text-center py-2 text-red-500">Error: No operator ID provided</div>';
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
        
        // Use the global variables set by openOperatorSelection
        const posteId = currentPosteId;
        const operatorId = currentOperatorId;
        
        console.log('Using stored posteId:', posteId, 'operatorId:', operatorId);
        console.log('Request payload:', {
            poste_id: posteId,
            operator_id: operatorId,
            backup_operator_id: operator.id,
            backup_slot: slot,
            assigned_date: new Date().toISOString().split('T')[0]
        });
        
        if (!posteId || !operatorId) {
            alert('Error: Missing poste ID or operator ID');
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
        
        // Get the backup assignment container for this row using a different approach
        // Since modals are now outside the table, we need to find the container by data attributes
        const containers = document.querySelectorAll('.backup-assignment-container');
        let container = null;
        
        // Find the container that matches our current operator ID
        for (let cont of containers) {
            if (cont.dataset.operatorId === currentOperatorId) {
                container = cont;
                break;
            }
        }
        
        if (!container) {
            console.error('Backup assignment container not found for operator ID:', currentOperatorId);
            return;
        }
        
        // Use the global operatorId
        const operatorId = currentOperatorId;
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
