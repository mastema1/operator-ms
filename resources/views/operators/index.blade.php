<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Operator Management</h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="GET" action="{{ route('operators.index') }}" class="flex items-center gap-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by matricule, name, poste" class="flex-1 border rounded px-3 py-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Search</button>
                    <a href="{{ route('operators.index', array_merge(request()->query(), ['critical_only' => request('critical_only') ? '' : '1'])) }}" 
                       class="px-4 py-2 rounded border transition-colors {{ request('critical_only') ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                        {{ request('critical_only') ? 'Show All' : 'Critical Only' }}
                    </a>
                </form>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm bg-white border rounded px-3 py-2">{{ $total }} Operators</div>
                    <a href="{{ route('operators.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Add Operator</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="p-3">Matricule</th>
                                <th class="p-3">First Name</th>
                                <th class="p-3">Last Name</th>
                                <th class="p-3">Poste</th>
                                <th class="p-3">Ligne</th>
                                <th class="p-3">Critical Status</th>
                                {{-- <th class="p-3">Ancienneté</th> --}}
                                {{-- <th class="p-3">Type de Contrat</th> --}}
                                <th class="p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($operators as $op)
                                <tr class="border-b">
                                    <td class="p-3">{{ $op->matricule }}</td>
                                    <td class="p-3">{{ $op->first_name }}</td>
                                    <td class="p-3">{{ $op->last_name }}</td>
                                    <td class="p-3">{{ $op->poste?->name }}</td>
                                    <td class="p-3">{{ $op->ligne }}</td>
                                    <td class="p-3">
                                        @php
                                            $positionKey = $op->poste_id . '_' . $op->ligne;
                                            
                                            // Three-tier priority system for critical status determination:
                                            // 1. If explicit critical_positions record exists (true) → Critical
                                            // 2. If explicit non-critical override exists (false) → Non-critical  
                                            // 3. If no specific record exists → Default to Non-critical
                                            
                                            $isCritical = false;
                                            
                                            if (isset($criticalPositions[$positionKey])) {
                                                // Explicit critical position record exists
                                                $isCritical = true;
                                            } elseif (isset($nonCriticalPositions[$positionKey])) {
                                                // Explicit non-critical override exists
                                                $isCritical = false;
                                            } else {
                                                // No specific record exists - default to non-critical
                                                $isCritical = false;
                                            }
                                        @endphp
                                        @if($isCritical)
                                            <span class="text-red-600 font-medium">Critical</span>
                                        @else
                                            <span class="text-green-600 font-medium">Non-critical</span>
                                        @endif
                                    </td>
                                    {{-- <td class="p-3">{{ $op->anciente }}</td> --}}
                                    {{-- <td class="p-3">{{ $op->type_de_contrat }}</td> --}}
                                    <td class="p-3 space-x-2">
                                        <a href="{{ route('operators.edit', $op) }}" class="px-3 py-1 border rounded">Edit</a>
                                        <form method="POST" action="{{ route('operators.destroy', $op) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this operator?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 border rounded text-red-700">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
                                            <a href="{{ $operators->appends(request()->query())->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        @endif

                                        {{-- Page Numbers --}}
                                        @foreach ($operators->appends(request()->query())->getUrlRange(1, $operators->lastPage()) as $page => $url)
                                            @if ($page == $operators->currentPage())
                                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-blue-600 border border-gray-300 cursor-default leading-5">{{ $page }}</span>
                                            @else
                                                <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                                    {{ $page }}
                                                </a>
                                            @endif
                                        @endforeach

                                        {{-- Next Page Link --}}
                                        @if ($operators->hasMorePages())
                                            <a href="{{ $operators->appends(request()->query())->nextPageUrl() }}" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
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
        </div>
    </div>
</x-app-layout>