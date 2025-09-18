<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Dashboard data check:\n";

// Simulate the dashboard controller logic
$criticalPostes = App\Models\Poste::where('is_critical', true)
    ->with([
        'operators:id,first_name,last_name,poste_id,ligne',
        'operators.attendances' => function ($query) {
            $query->whereDate('date', today())
                  ->select('id', 'operator_id', 'date', 'status');
        },
        'backupAssignments' => function ($query) {
            $query->whereDate('assigned_date', today())
                  ->with('backupOperator:id,first_name,last_name')
                  ->orderBy('backup_slot');
        }
    ])
    ->select('id', 'name')
    ->get();

echo "Critical postes found: " . $criticalPostes->count() . "\n";

$criticalPostesWithOperators = $criticalPostes->flatMap(function ($poste) {
    return $poste->operators->map(function ($operator) use ($poste) {
        $todayAttendance = $operator->attendances->first();
        $isPresent = !$todayAttendance || $todayAttendance->status === 'present';
        
        return [
            'poste_id' => $poste->id,
            'ligne' => $operator->ligne ?? 'Ligne1',
            'poste_name' => $poste->name,
            'operator_name' => $operator->first_name . ' ' . $operator->last_name,
            'is_present' => $isPresent,
            'backup_assignments' => $poste->backupAssignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'slot' => $assignment->backup_slot,
                    'operator_name' => $assignment->backupOperator->first_name . ' ' . $assignment->backupOperator->last_name,
                    'operator_id' => $assignment->backup_operator_id
                ];
            })
        ];
    });
});

echo "Critical postes with operators: " . $criticalPostesWithOperators->count() . "\n";

if ($criticalPostesWithOperators->count() > 0) {
    echo "First few assignments:\n";
    foreach ($criticalPostesWithOperators->take(3) as $index => $assignment) {
        echo "Row {$index}: {$assignment['operator_name']} at {$assignment['poste_name']} - Backups: " . count($assignment['backup_assignments']) . "\n";
    }
} else {
    echo "No critical poste assignments found - dashboard will show empty state\n";
}
