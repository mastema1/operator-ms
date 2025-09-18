<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Operator</h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 text-red-700 bg-red-100 border border-red-200 rounded p-3">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('operators.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-700">Matricule</label>
                        <input name="matricule" type="text" value="{{ old('matricule') }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700">First Name</label>
                        <input name="first_name" type="text" value="{{ old('first_name') }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700">Last Name</label>
                        <input name="last_name" type="text" value="{{ old('last_name') }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700">Poste</label>
                        <select name="poste_id" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select a poste</option>
                            @foreach ($postes as $poste)
                                <option value="{{ $poste->id }}" @selected(old('poste_id') == $poste->id)>{{ $poste->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700">Critical Status</label>
                        <select name="is_critical" class="w-full border rounded px-3 py-2">
                            <option value="0" @selected(old('is_critical') == '0')>Non-critical</option>
                            <option value="1" @selected(old('is_critical') == '1')>Critical</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Critical status applies to the specific Poste + Ligne combination</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="capable" name="is_capable" type="checkbox" value="1" @checked(old('is_capable', true)) class="border rounded">
                        <label for="capable" class="text-gray-700">Polyvalence</label>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700">Ancienneté</label>
                        <input name="anciente" type="text" value="{{ old('anciente') }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700">Type de Contrat</label>
                        <select name="type_de_contrat" class="w-full border rounded px-3 py-2">
                            <option value="">Select type</option>
                            <option value="ANAPEC" @selected(old('type_de_contrat')==='ANAPEC')>ANAPEC</option>
                            <option value="AWRACH" @selected(old('type_de_contrat')==='AWRACH')>AWRACH</option>
                            <option value="TES" @selected(old('type_de_contrat')==='TES')>TES</option>
                            <option value="CDI" @selected(old('type_de_contrat')==='CDI')>CDI</option>
                            <option value="CDD 6 mois" @selected(old('type_de_contrat')==='CDD 6 mois')>CDD 6 mois</option>
                            <option value="CDD 1 an" @selected(old('type_de_contrat')==='CDD 1 an')>CDD 1 an</option>
                            <option value="CDD 2 ans" @selected(old('type_de_contrat')==='CDD 2 ans')>CDD 2 ans</option>
                            <option value="CDD 3 ans" @selected(old('type_de_contrat')==='CDD 3 ans')>CDD 3 ans</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700">Ligne</label>
                        <select name="ligne" class="w-full border rounded px-3 py-2">
                            <option value="">Select a ligne</option>
                            <option value="Ligne 1" @selected(old('ligne') === 'Ligne 1')>Ligne 1</option>
                            <option value="Ligne 2" @selected(old('ligne') === 'Ligne 2')>Ligne 2</option>
                            <option value="Ligne 3" @selected(old('ligne') === 'Ligne 3')>Ligne 3</option>
                            <option value="Ligne 4" @selected(old('ligne') === 'Ligne 4')>Ligne 4</option>
                            <option value="Ligne 5" @selected(old('ligne') === 'Ligne 5')>Ligne 5</option>
                            <option value="Ligne 6" @selected(old('ligne') === 'Ligne 6')>Ligne 6</option>
                            <option value="Ligne 7" @selected(old('ligne') === 'Ligne 7')>Ligne 7</option>
                            <option value="Ligne 8" @selected(old('ligne') === 'Ligne 8')>Ligne 8</option>
                            <option value="Ligne 9" @selected(old('ligne') === 'Ligne 9')>Ligne 9</option>
                            <option value="Ligne 10" @selected(old('ligne') === 'Ligne 10')>Ligne 10</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('operators.index') }}" class="px-4 py-2 border rounded text-gray-700">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout> 