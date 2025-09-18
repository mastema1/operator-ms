<div class="p-6 space-y-6" wire:init="ping">
    @if (session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <div class="flex-1 flex gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by matricule, name, poste..." class="flex-1 border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            <button wire:click="toggleCriticalFilter" class="px-4 py-2 rounded border transition-colors {{ $showCriticalOnly ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                {{ $showCriticalOnly ? 'Show All' : 'Critical Only' }}
            </button>
        </div>
        <div class="text-sm bg-white border rounded px-3 py-2">{{ $total }} Operators</div>
        @if (request()->routeIs('operators.index'))
            <a href="{{ route('operators.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded inline-flex items-center">Add Operator</a>
        @else
            <button wire:click="openCreate" class="bg-blue-600 text-white px-4 py-2 rounded">Add Operator</button>
        @endif
    </div>

    <div class="bg-white rounded border overflow-x-auto">
        <table class="w-full text-left table-auto" style="min-width: 1300px;">
            <thead>
                <tr class="border-b">
                    <th class="p-3 w-24">Matricule</th>
                    <th class="p-3 w-32">First Name</th>
                    <th class="p-3 w-32">Last Name</th>
                    <th class="p-3 w-28">Poste</th>
                    {{-- <th class="p-3 w-28">Ancienneté</th> --}}
                    {{-- <th class="p-3 w-36">Type de Contrat</th> --}}
                    <th class="p-3 w-28">Ligne</th>
                    <th class="p-3 w-32">Poste Critical</th>
                    <th class="p-3 w-32">Operator Critical</th>
                    <th class="p-3 w-28">Capability</th>
                    <th class="p-3 w-52">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($operators as $op)
                    <tr class="border-b">
                        <td class="p-3">{{ $op->matricule }}</td>
                        <td class="p-3">{{ $op->first_name }}</td>
                        <td class="p-3">{{ $op->last_name }}</td>
                        <td class="p-3">{{ $op->poste?->name }}</td>
                        {{-- <td class="p-3">{{ $op->anciente }}</td> --}}
                        {{-- <td class="p-3">{{ $op->type_de_contrat }}</td> --}}
                        <td class="p-3">{{ $op->ligne }}</td>
                        <td class="p-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $op->poste?->is_critical ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $op->poste?->is_critical ? 'Critical' : 'Non-Critical' }}
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $op->is_critical ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $op->is_critical ? 'Critical' : 'Non-Critical' }}
                            </span>
                        </td>
                        <td class="p-3">
                            <button wire:click="toggleCapability({{ $op->id }})" class="px-3 py-1 rounded text-white {{ $op->is_capable ? 'bg-green-600' : 'bg-red-600' }}">
                                {{ $op->is_capable ? 'Capable' : 'Not Capable' }}
                            </button>
                        </td>
                        <td class="p-3 whitespace-nowrap w-52">
                            @if (request()->routeIs('operators.index'))
                                <a href="{{ route('operators.edit', $op) }}" class="px-3 py-1 border rounded mr-4 inline-block">Edit</a>
                            @else
                                <button wire:click="edit({{ $op->id }})" class="px-3 py-1 border rounded mr-4 inline-block">Edit</button>
                            @endif
                            <button wire:click="confirm('Are you sure you want to delete this operator?') && delete({{ $op->id }})" class="px-3 py-1 border rounded text-red-700 inline-block">Delete</button>
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
                                    <button wire:click="previousPage" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150">
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
                                        <button wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($operators->hasMorePages())
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

    @if($showModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-xl rounded shadow p-6">
            <h2 class="text-lg font-semibold mb-4">{{ $isEdit ? 'Edit Operator' : 'Add Operator' }}</h2>
            <form wire:submit.prevent="save" class="space-y-4">
                <div>
                    <label for="matricule" class="block text-sm font-medium text-gray-700">Matricule</label>
                    <input type="text" wire:model="matricule" id="matricule" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('matricule') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" wire:model="first_name" id="first_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" wire:model="last_name" id="last_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div style="background-color: red; padding: 10px; color: white; font-weight: bold;">
                    DEBUG: LIGNE FIELD SHOULD BE HERE
                </div>
                
                <div>
                    <label for="ligne" class="block text-sm font-medium text-gray-700">Ligne</label>
                    <input type="text" wire:model="ligne" id="ligne" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Enter ligne">
                    @error('ligne') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="poste_id" class="block text-sm font-medium text-gray-700">Poste</label>
                    <select wire:model="poste_id" id="poste_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select a Poste</option>
                        @foreach($postes as $poste)
                            <option value="{{ $poste->id }}">{{ $poste->name }}</option>
                        @endforeach
                    </select>
                    @error('poste_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="anciente" class="block text-sm font-medium text-gray-700">Ancienneté</label>
                    <input type="text" wire:model="anciente" id="anciente" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('anciente') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="type_de_contrat" class="block text-sm font-medium text-gray-700">Type de Contrat</label>
                    <select wire:model="type_de_contrat" id="type_de_contrat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Contract Type</option>
                        <option value="ANAPEC">ANAPEC</option>
                        <option value="AWRACH">AWRACH</option>
                        <option value="TES">TES</option>
                        <option value="CDI">CDI</option>
                        <option value="CDD 6 mois">CDD 6 mois</option>
                        <option value="CDD 1 ans">CDD 1 ans</option>
                        <option value="CDD 2 ans">CDD 2 ans</option>
                        <option value="CDD 3 ans">CDD 3 ans</option>
                    </select>
                    @error('type_de_contrat') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" wire:model="is_capable" id="is_capable" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_capable" class="ml-2 block text-sm text-gray-900">Is Capable</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" wire:model="is_critical" id="is_critical" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <label for="is_critical" class="ml-2 block text-sm text-gray-900">Is Critical</label>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        {{ $isEdit ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>