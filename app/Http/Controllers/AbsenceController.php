<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Operator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        
        $operators = Operator::with(['poste', 'attendances' => function ($q) { $q->forToday(); }])
            ->when($search, function (Builder $q) use ($search) {
                $term = '%'.$search.'%';
                $q->where(function (Builder $sub) use ($term) {
                    $sub->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhereHas('poste', function (Builder $p) use ($term) { 
                            $p->where('name', 'like', $term); 
                        });
                });
            })
            ->orderBy('last_name')
            ->paginate(15, ['*'], 'page', $page);

        // Only return partial view for actual AJAX requests
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('partials.absence-table', compact('operators'))->render();
        }

        // For regular requests (like pagination clicks), redirect to Livewire component
        return redirect()->route('absences.index', $request->query());
    }

    public function toggle(Request $request, Operator $operator)
    {
        $today = today();
        $attendance = Attendance::firstOrNew([
            'operator_id' => $operator->id,
            'date' => $today,
        ]);
        
        $attendance->status = $attendance->exists && $attendance->status === 'absent' ? 'present' : 'absent';
        $attendance->save();

        $isAbsent = $attendance->status === 'absent';
        
        return response()->json([
            'success' => true,
            'status' => $attendance->status,
            'isAbsent' => $isAbsent,
            'buttonText' => $isAbsent ? 'Absent' : 'Present',
            'buttonClass' => $isAbsent ? 'bg-red-600' : 'bg-green-600',
            'counters' => $this->getAttendanceCounters()
        ]);
    }

    private function getAttendanceCounters()
    {
        $total = Operator::count();
        $absent = Attendance::forToday()->where('status', 'absent')->count();
        $present = $total - $absent;

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent
        ];
    }
}
