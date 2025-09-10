<div class="p-6 space-y-6">
    <h1 class="text-xl font-semibold">Gestion d'absence pour le {{ $todayFr }}</h1>
    
    {{-- Flash messages --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border rounded p-4 counter-card">Total Operators: <strong>{{ $total }}</strong></div>
        <div class="bg-white border rounded p-4 counter-card">Operators Present: <strong>{{ $present }}</strong></div>
        <div class="bg-white border rounded p-4 counter-card">Operators Absent: <strong>{{ $absent }}</strong></div>
    </div>

    <div class="flex flex-col md:flex-row gap-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or poste" class="w-full md:w-1/3 border rounded px-3 py-2" />
        <select wire:model.live="ligneFilter" class="w-full md:w-1/4 border rounded px-3 py-2">
            <option value="">All Lignes</option>
            @foreach($lignes as $ligne)
                <option value="{{ $ligne }}">{{ $ligne }}</option>
            @endforeach
        </select>
        <button wire:click="$refresh" class="px-4 py-2 bg-blue-500 text-white rounded">Refresh</button>
    </div>

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
            <tbody>
                @foreach ($operators as $op)
                    @php $todayAttendance = $op->attendances->first(); @endphp
                    <tr class="border-b">
                        <td class="p-3">{{ $op->first_name }}</td>
                        <td class="p-3">{{ $op->last_name }}</td>
                        <td class="p-3">{{ $op->poste?->name }}</td>
                        <td class="p-3">{{ $op->ligne }}</td>
                        <td class="p-3">
                            @php $isAbsent = $todayAttendance && $todayAttendance->status === 'absent'; @endphp
                            <button wire:click="toggleAttendance({{ $op->id }})" 
                                    wire:key="toggle-{{ $op->id }}-{{ $isAbsent ? 'absent' : 'present' }}"
                                    type="button"
                                    class="px-3 py-1 rounded text-white {{ $isAbsent ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} transition-colors">
                                {{ $isAbsent ? 'Absent' : 'Present' }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        @if ($operators->hasPages())
            <div class="mt-4">
                {{ $operators->links() }}
            </div>
        @endif
    </div>

</div>