<?php

namespace App\Livewire;

use App\Http\Requests\StoreOperatorRequest;
use App\Http\Requests\UpdateOperatorRequest;
use App\Models\Operator;
use App\Models\Poste;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Operators extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $search = '';
    public bool $showModal = false;
    public bool $isEdit = false;
    public bool $showCriticalOnly = false;

    #[Validate('nullable|string|max:255|unique:operators,matricule')] public ?string $matricule = null;
    #[Validate('required|string|max:255')] public string $first_name = '';
    #[Validate('required|string|max:255')] public string $last_name = '';
    #[Validate('required|exists:postes,id')] public $poste_id = '';
    public bool $is_capable = true;
    public bool $is_critical = false;
    #[Validate('nullable|string|max:255')] public ?string $anciente = null;
    #[Validate('nullable|string|in:ANAPEC,AWRACH,TES,CDI,CDD 6 mois,CDD 1 ans,CDD 2 ans,CDD 3 ans')] public ?string $type_de_contrat = null;
    #[Validate('nullable|string|max:255')] public ?string $ligne = null;

    public ?int $editingId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingShowCriticalOnly(): void
    {
        $this->resetPage();
    }

    public function toggleCriticalFilter(): void
    {
        $this->showCriticalOnly = !$this->showCriticalOnly;
        $this->resetPage();
    }


    public function openCreate(): void
    {
        $this->reset(['matricule','first_name','last_name','poste_id','is_capable','is_critical','anciente','type_de_contrat','ligne']);
        $this->isEdit = false;
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    public function edit(int $id): void
    {
        $operator = Operator::findOrFail($id);
        $this->editingId = $operator->id;
        $this->matricule = $operator->matricule;
        $this->first_name = $operator->first_name;
        $this->last_name = $operator->last_name;
        $this->poste_id = (string)$operator->poste_id;
        $this->is_capable = (bool)$operator->is_capable;
        $this->is_critical = (bool)$operator->is_critical;
        $this->anciente = $operator->anciente;
        $this->type_de_contrat = $operator->type_de_contrat;
        $this->ligne = $operator->ligne;
        $this->isEdit = true;
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    public function rules(): array
    {
        $matriculeRule = 'nullable|string|max:255|unique:operators,matricule';
        if ($this->isEdit && $this->editingId) {
            $matriculeRule .= ',' . $this->editingId;
        }

        return [
            'matricule' => $matriculeRule,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'poste_id' => 'required|exists:postes,id',
            'is_capable' => 'boolean',
            'is_critical' => 'boolean',
            'anciente' => 'nullable|string|max:255',
            'type_de_contrat' => 'nullable|string|in:ANAPEC,AWRACH,TES,CDI,CDD 6 mois,CDD 1 ans,CDD 2 ans,CDD 3 ans',
            'ligne' => 'nullable|string|max:255',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            if ($this->isEdit && $this->editingId) {
                $operator = Operator::findOrFail($this->editingId);
                
                // Debug logging
                \Log::info('Updating operator', [
                    'operator_id' => $this->editingId,
                    'old_is_critical' => $operator->is_critical,
                    'new_is_critical' => $data['is_critical'],
                    'all_data' => $data
                ]);
                
                $operator->update($data);
                
                // Verify the update
                $operator->refresh();
                \Log::info('After update', [
                    'operator_id' => $this->editingId,
                    'current_is_critical' => $operator->is_critical
                ]);
                
                session()->flash('success', 'Operator updated successfully.');
            } else {
                Operator::create($data);
                session()->flash('success', 'Operator created successfully.');
            }
        });

        $this->showModal = false;
        $this->dispatch('close-modal');
    }

    public function delete(int $id): void
    {
        $operator = Operator::find($id);
        if ($operator) {
            $operator->delete();
            session()->flash('success', 'Operator deleted successfully.');
            $this->resetPage();
        }
    }

    public function toggleCapability(int $id): void
    {
        $operator = Operator::findOrFail($id);
        $operator->is_capable = !$operator->is_capable;
        $operator->save();
    }

    public function render()
    {

        $postes = Poste::query()
            ->orderByRaw("CASE WHEN name REGEXP '^Poste [0-9]+' THEN CAST(SUBSTRING(name, 7) AS UNSIGNED) ELSE 100000 END")
            ->orderBy('name')
            ->get();
        $operators = Operator::with('poste')
            ->when(strlen($this->search) > 0, function (Builder $q) {
                $term = '%'.$this->search.'%';
                $q->where(function (Builder $sub) use ($term) {
                    $sub->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('matricule', 'like', $term)
                        ->orWhere('anciente', 'like', $term)
                        ->orWhere('type_de_contrat', 'like', $term)
                        ->orWhere('ligne', 'like', $term)
                        ->orWhereHas('poste', function (Builder $p) use ($term) {
                            $p->where('name', 'like', $term);
                        });
                });
            })
            ->when($this->showCriticalOnly, function (Builder $q) {
                $q->whereHas('poste', function (Builder $p) {
                    $p->where('is_critical', true);
                });
            })
            ->orderBy('last_name')
            ->paginate(15);

        $total = Operator::count();

        return view('livewire.operators', compact('operators','postes','total'))
            ->layout('layouts.app');
    }
} 