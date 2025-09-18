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
                            <p class="text-3xl font-bold text-green-600">8</p>
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
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
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
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
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
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
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
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
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
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Poste 22
                                    </span>
                                </td>
                                <td class="p-3 text-sm text-gray-800 font-medium">Mohssine Ait El Walid</td>
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
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Poste 23
                                    </span>
                                </td>
                                <td class="p-3 text-sm text-gray-800 font-medium">Omar El yakhlafi</td>
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
                                                <li>• Abdellah Howari</li>
                                                <li>• Amine Fakir</li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="p-3 text-sm text-gray-800 relative">ligne 1</td>
                                <td class="p-3 text-sm text-gray-800 relative">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
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
                                                <li>• Khalid Sbaibi</li>
                                                <li>• Rajae El Fassi</li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleBackupPopover(button) {
            // Close all other popovers first
            document.querySelectorAll('.backup-popover').forEach(popover => {
                if (popover !== button.nextElementSibling) {
                    popover.classList.add('hidden');
                }
            });
            
            // Toggle current popover
            const popover = button.nextElementSibling;
            popover.classList.toggle('hidden');
        }

        // Close popovers when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.backup-popover') && !event.target.closest('button')) {
                document.querySelectorAll('.backup-popover').forEach(popover => {
                    popover.classList.add('hidden');
                });
            }
        });
    </script>
</x-app-layout>
