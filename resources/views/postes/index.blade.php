<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900">Postes Management</h1>
                <a href="{{ route('postes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Add New Poste
                </a>
            </div>
        </div>

        <div class="p-6">
            <!-- Search Form -->
            <form method="GET" class="mb-6">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ $search }}" 
                               placeholder="Search postes or operators..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                        Search
                    </button>
                    @if($search)
                        <a href="{{ route('postes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md">
                            Clear
                        </a>
                    @endif
                </div>
            </form>

            <!-- Results Summary -->
            <div class="mb-4 text-sm text-gray-600">
                Showing {{ $postes->count() }} of {{ $total }} postes
                @if($search)
                    for "{{ $search }}"
                @endif
            </div>

            <!-- Postes Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ligne</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operators</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($postes as $poste)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $poste->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $poste->ligne ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($poste->is_critical)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Critical
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Non-critical
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if($poste->operators->count() > 0)
                                        {{ $poste->operators->pluck('full_name')->join(', ') }}
                                    @else
                                        <span class="text-gray-400 italic">No operators assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('postes.edit', $poste) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form method="POST" action="{{ route('postes.destroy', $poste) }}" 
                                              class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this poste?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    @if($search)
                                        No postes found matching "{{ $search }}"
                                    @else
                                        No postes found
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($postes->hasPages())
                <div class="mt-6">
                    {{ $postes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>
