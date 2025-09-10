<div class="bg-white rounded border">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b">
                <th class="p-3">First Name</th>
                <th class="p-3">Last Name</th>
                <th class="p-3">Poste</th>
                <th class="p-3">Ligne</th>
                <th class="p-3">Absence Status</th>
            </tr>
        </thead>
        <tbody id="operator-table-body">
            @foreach ($operators as $op)
                @php $todayAttendance = $op->attendances->first(); @endphp
                <tr class="border-b">
                    <td class="p-3">{{ $op->first_name }}</td>
                    <td class="p-3">{{ $op->last_name }}</td>
                    <td class="p-3">{{ $op->poste?->name }}</td>
                    <td class="p-3">{{ $op->ligne }}</td>
                    <td class="p-3">
                        @php $isAbsent = $todayAttendance && $todayAttendance->status === 'absent'; @endphp
                        <button wire:click="toggleAttendance({{ $op->id }})" class="px-3 py-1 rounded text-white {{ $isAbsent ? 'bg-red-600' : 'bg-green-600' }}">
                            {{ $isAbsent ? 'Absent' : 'Present' }}
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">
        @if ($operators->hasPages())
            <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 leading-5">
                            Showing
                            @if ($operators->firstItem())
                                <span class="font-medium">{{ $operators->firstItem() }}</span>
                                to
                                <span class="font-medium">{{ $operators->lastItem() }}</span>
                            @else
                                {{ $operators->count() }}
                            @endif
                            of
                            <span class="font-medium">{{ $operators->total() }}</span>
                            results
                        </p>
                    </div>

                    <div>
                        <span class="relative z-0 inline-flex shadow-sm rounded-md">
                            {{-- Previous Page Link --}}
                            @if ($operators->onFirstPage())
                                <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @else
                                <button onclick="loadPage({{ $operators->currentPage() - 1 }})" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif

                            {{-- Page Numbers --}}
                            @foreach ($operators->getUrlRange(1, $operators->lastPage()) as $page => $url)
                                @if ($page == $operators->currentPage())
                                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-blue-600 border border-gray-300 cursor-default leading-5">{{ $page }}</span>
                                @else
                                    <button onclick="loadPage({{ $page }})" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($operators->hasMorePages())
                                <button onclick="loadPage({{ $operators->currentPage() + 1 }})" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
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

<script>
    function loadPage(page) {
        // Make an AJAX request to load the next page
        // Replace the table body with the new data
        // Update the pagination links
    }
</script>
