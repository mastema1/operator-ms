<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900">Add New Poste</h1>
                <a href="{{ route('postes.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Back to Postes
                </a>
            </div>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('postes.store') }}" class="space-y-6">
                @csrf

                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Poste Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ligne Field -->
                <div>
                    <label for="ligne" class="block text-sm font-medium text-gray-700 mb-2">
                        Ligne
                    </label>
                    <select id="ligne" 
                            name="ligne" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ligne') border-red-500 @enderror">
                        <option value="">Select Ligne (Optional)</option>
                        <option value="Ligne 1" {{ old('ligne') == 'Ligne 1' ? 'selected' : '' }}>Ligne 1</option>
                        <option value="Ligne 2" {{ old('ligne') == 'Ligne 2' ? 'selected' : '' }}>Ligne 2</option>
                        <option value="Ligne 3" {{ old('ligne') == 'Ligne 3' ? 'selected' : '' }}>Ligne 3</option>
                        <option value="Ligne 4" {{ old('ligne') == 'Ligne 4' ? 'selected' : '' }}>Ligne 4</option>
                        <option value="Ligne 5" {{ old('ligne') == 'Ligne 5' ? 'selected' : '' }}>Ligne 5</option>
                        <option value="Ligne 6" {{ old('ligne') == 'Ligne 6' ? 'selected' : '' }}>Ligne 6</option>
                        <option value="Ligne 7" {{ old('ligne') == 'Ligne 7' ? 'selected' : '' }}>Ligne 7</option>
                        <option value="Ligne 8" {{ old('ligne') == 'Ligne 8' ? 'selected' : '' }}>Ligne 8</option>
                        <option value="Ligne 9" {{ old('ligne') == 'Ligne 9' ? 'selected' : '' }}>Ligne 9</option>
                        <option value="Ligne 10" {{ old('ligne') == 'Ligne 10' ? 'selected' : '' }}>Ligne 10</option>
                    </select>
                    @error('ligne')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Critical Status Field -->
                <div>
                    <label for="is_critical" class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select id="is_critical" 
                            name="is_critical" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('is_critical') border-red-500 @enderror">
                        <option value="0" {{ old('is_critical', '0') == '0' ? 'selected' : '' }}>Non-critical</option>
                        <option value="1" {{ old('is_critical') == '1' ? 'selected' : '' }}>Critical</option>
                    </select>
                    @error('is_critical')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('postes.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">
                        Create Poste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-app-layout>
