<!--<div class="p-6 space-y-6" x-data="{ showBackup: $wire.entangle('showBackupModal') }">
    <div class="flex items-center justify-between">
        <div></div>
        <div>État de poste en {{ $todayFr }}</div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white border rounded p-4">Critical Posts Occupied: <strong>{{ $criticalOccupied }}</strong></div>
        <div class="bg-white border rounded p-4">Critical Posts Vacant: <strong>{{ $criticalVacant }}</strong></div>
    </div>

    <div class="bg-white rounded border">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b">
                    <th class="p-3">Poste</th>
                    <th class="p-3">Criticality</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Operator</th>
                    <th class="p-3">Presence</th>
                    <th class="p-3">Backups</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                    @php
                        $isOccupied = $entry->operator && $entry->present;
                    @endphp
                    <tr class="border-b">
                        <td class="p-3">{{ $entry->poste->name }}</td>
                        <td class="p-3">
                            @if($entry->poste->is_critical)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Critical
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Non-critical
                                </span>
                            @endif
                        </td>
                        <td class="p-3">
                            <span class="{{ $isOccupied ? 'text-green-700' : 'text-red-700' }}">
                                {{ $isOccupied ? 'Occupied' : 'Vacant' }}
                            </span>
                        </td>
                        <td class="p-3">{{ $isOccupied ? $entry->operator->full_name : 'N/A' }}</td>
                        <td class="p-3">{!! $isOccupied ? '&#x2705;' : '&#x274C;' !!}</td>
                        <td class="p-3">
                            @if (! $isOccupied)
                                <button class="text-blue-700 underline" @click="$wire.openBackups({{ $entry->poste->id }})">Find Backup</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            @if ($entries->hasPages())
                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 leading-5">
                                Showing
                                @if ($entries->firstItem())
                                    <span class="font-medium">{{ $entries->firstItem() }}</span>
                                    to
                                    <span class="font-medium">{{ $entries->lastItem() }}</span>
                                @else
                                    {{ $entries->count() }}
                                @endif
                                of
                                <span class="font-medium">{{ $entries->total() }}</span>
                                results
                            </p>
                        </div>

                        <div>
                            <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                {{-- Previous Page Link --}}
                                @if ($entries->onFirstPage())
                                    <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @else
                                    <button wire:click="previousPage" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif

                                {{-- Page Numbers --}}
                                @foreach ($entries->getUrlRange(1, $entries->lastPage()) as $page => $url)
                                    @if ($page == $entries->currentPage())
                                        <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-blue-600 border border-gray-300 cursor-default leading-5">{{ $page }}</span>
                                    @else
                                        <button wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($entries->hasMorePages())
                                    <button wire:click="nextPage" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @else
                                    <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md leading-5">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>
                </nav>
            @endif
        </div>
    </div>

    <div x-cloak x-show="showBackup" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div @click.outside="showBackup=false" class="bg-white w-full max-w-2xl rounded shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Available Backups</h2>
            <ul class="space-y-2 max-h-96 overflow-auto">
                @forelse ($this->backupCandidates as $op)
                    <li class="border rounded p-3">{{ $op->full_name }} — {{ $op->poste?->name }}</li>
                @empty
                    <li class="text-sm text-gray-600">No backup candidates available.</li>
                @endforelse
            </ul>
            <div class="text-right mt-4">
                <button class="px-4 py-2 border rounded" @click="showBackup=false">Close</button>
            </div>
        </div>
    </div>
</div>
-->