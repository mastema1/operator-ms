<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Operator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\DashboardCacheManager;

class Absences extends Component
{
    use WithPagination;

    public string $search = '';
    public string $ligneFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingLigneFilter(): void
    {
        $this->resetPage();
    }

    public function toggleAttendance($operatorId)
    {
        try {
            $operator = Operator::findOrFail($operatorId);
            
            // Get today's attendance record
            $attendance = Attendance::where('operator_id', $operatorId)
                ->whereDate('date', today())
                ->first();
            
            if ($attendance) {
                // Toggle the status
                $attendance->status = $attendance->status === 'present' ? 'absent' : 'present';
                $attendance->save();
            } else {
                // Create new attendance record as absent (since default assumption is present)
                Attendance::create([
                    'operator_id' => $operatorId,
                    'date' => today(),
                    'status' => 'absent'
                ]);
            }
            
            // Clear dashboard cache when attendance is updated using centralized cache manager
            DashboardCacheManager::clearOnAttendanceChange();
            
            // Add a session flash message to confirm the action
            session()->flash('message', 'Attendance updated successfully');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating attendance: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $operators = Operator::with(['poste', 'attendances' => function ($q) {
            $q->forToday()->select('id', 'operator_id', 'date', 'status');
        }])
            ->select('id', 'first_name', 'last_name', 'poste_id', 'ligne')
            ->when($this->search, function (Builder $q) {
                $term = '%'.$this->search.'%';
                $q->where(function (Builder $sub) use ($term) {
                    $sub->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhereHas('poste', function (Builder $p) use ($term) {
                            $p->where('name', 'like', $term);
                        });
                });
            })
            ->when($this->ligneFilter, function (Builder $q) {
                $q->where('ligne', $this->ligneFilter);
            })
            ->orderBy('last_name')
            ->paginate(15);

        $total = Operator::count();
        $absent = Attendance::forToday()->where('status', 'absent')->count();
        $present = $total - $absent;

        $todayFr = now()->locale('fr_FR')->isoFormat('dddd D MMMM YYYY');

        $lignes = Operator::select('ligne')
            ->distinct()
            ->whereNotNull('ligne')
            ->get()
            ->pluck('ligne')
            ->sort(function ($a, $b) {
                // Extract numeric part for natural sorting
                $numA = (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT);
                $numB = (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);
                return $numA <=> $numB;
            })
            ->values();

        return view('livewire.absences', compact('operators','total','present','absent','todayFr','lignes'))
            ->layout('layouts.app');
    }
}