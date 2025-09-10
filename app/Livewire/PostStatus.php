<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Operator;
use App\Models\Poste;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class PostStatus extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $showBackupModal = false;
    public ?int $selectedPosteId = null;

    public function openBackups(int $posteId): void
    {
        $this->selectedPosteId = $posteId;
        $this->showBackupModal = true;
    }

    public function getBackupCandidatesProperty()
    {
        if (!$this->selectedPosteId) return collect();
        $today = today();
        return Operator::with('poste')
            ->where('poste_id', $this->selectedPosteId)
            ->whereDoesntHave('attendances', function (Builder $q) use ($today) {
                $q->whereDate('date', $today)->where('status', 'absent');
            })
            ->orderBy('last_name')
            ->get();
    }

    public function render()
    {
        $today = today();
        
        // Get paginated postes first
        $paginatedPostes = Poste::orderBy('name')->paginate(15);
        
        // Transform each item in the collection while preserving pagination
        $paginatedPostes->getCollection()->transform(function ($poste) use ($today) {
            $operator = Operator::where('poste_id', $poste->id)->first();
            $present = false;
            if ($operator) {
                $attendance = Attendance::where('operator_id', $operator->id)->whereDate('date', $today)->first();
                $present = !$attendance || $attendance->status === 'present';
            }
            return (object) [
                'poste' => $poste,
                'operator' => $operator,
                'present' => $operator ? $present : false,
            ];
        });

        $criticalOccupied = $paginatedPostes->filter(fn($p) => $p->poste->is_critical && $p->operator && $p->present)->count();
        $criticalVacant = $paginatedPostes->filter(fn($p) => $p->poste->is_critical && (!$p->operator || !$p->present))->count();

        $todayFr = now()->locale('fr_FR')->isoFormat('dddd D MMMM YYYY');

        return view('livewire.post-status', [
            'entries' => $paginatedPostes,
            'criticalOccupied' => $criticalOccupied,
            'criticalVacant' => $criticalVacant,
            'todayFr' => $todayFr,
        ])->layout('layouts.app');
    }
} 