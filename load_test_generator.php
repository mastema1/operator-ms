<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Poste;
use App\Models\Operator;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class LoadTestGenerator
{
    private $testSizes = [100, 500, 1000, 2000, 5000];
    private $results = [];

    public function runTests()
    {
        echo "=== LOAD TEST GENERATOR ===\n\n";
        
        foreach ($this->testSizes as $size) {
            echo "Testing with {$size} operators...\n";
            
            // Backup current data
            $this->backupCurrentData();
            
            // Generate test data
            $this->generateTestData($size);
            
            // Run performance tests
            $results = $this->runPerformanceTests($size);
            $this->results[$size] = $results;
            
            // Restore original data
            $this->restoreOriginalData();
            
            echo "Completed test for {$size} operators\n\n";
        }
        
        $this->generateReport();
    }
    
    private function backupCurrentData()
    {
        // Export current data to backup files
        $operators = Operator::all()->toArray();
        $postes = Poste::all()->toArray();
        $attendances = Attendance::all()->toArray();
        
        file_put_contents('backup_operators.json', json_encode($operators));
        file_put_contents('backup_postes.json', json_encode($postes));
        file_put_contents('backup_attendances.json', json_encode($attendances));
    }
    
    private function generateTestData($operatorCount)
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Attendance::truncate();
        Operator::truncate();
        Poste::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Generate postes (1 poste per 10 operators)
        $posteCount = max(10, intval($operatorCount / 10));
        $postes = [];
        
        for ($i = 1; $i <= $posteCount; $i++) {
            $postes[] = Poste::create([
                'name' => "TestPoste{$i}",
                'is_critical' => $i <= ($posteCount * 0.2), // 20% critical
            ]);
        }
        
        // Generate operators
        $operators = [];
        for ($i = 1; $i <= $operatorCount; $i++) {
            $operators[] = Operator::create([
                'first_name' => "TestOperator{$i}",
                'last_name' => "LastName{$i}",
                'matricule' => "MAT{$i}",
                'ligne' => 'Ligne' . (($i % 3) + 1),
                'anciente' => rand(1, 10),
                'type_de_contrat' => 'CDI',
                'poste_id' => $postes[array_rand($postes)]->id,
            ]);
        }
        
        // Generate attendance records (last 30 days)
        for ($day = 0; $day < 30; $day++) {
            $date = now()->subDays($day);
            
            foreach ($operators as $operator) {
                // 85% attendance rate
                if (rand(1, 100) <= 85) {
                    Attendance::create([
                        'operator_id' => $operator->id,
                        'date' => $date->format('Y-m-d'),
                        'status' => rand(1, 100) <= 95 ? 'present' : 'absent',
                    ]);
                }
            }
        }
    }
    
    private function runPerformanceTests($size)
    {
        $results = [
            'operator_count' => $size,
            'memory_usage' => 0,
            'peak_memory' => 0,
            'dashboard_time' => 0,
            'operators_time' => 0,
            'absences_time' => 0,
            'postes_time' => 0,
            'query_count' => 0,
        ];
        
        // Memory before tests
        $memoryBefore = memory_get_usage();
        
        // Dashboard performance
        DB::enableQueryLog();
        $start = microtime(true);
        
        $criticalPostes = Poste::where('is_critical', true)
            ->with(['operators.attendances' => function ($query) {
                $query->whereDate('date', today());
            }])
            ->get();
            
        $occupiedCount = $criticalPostes->filter(function ($poste) {
            if ($poste->operators->isEmpty()) return false;
            return $poste->operators->filter(function ($operator) {
                $todayAttendance = $operator->attendances->first();
                return !$todayAttendance || $todayAttendance->status === 'present';
            })->isNotEmpty();
        })->count();
        
        $results['dashboard_time'] = (microtime(true) - $start) * 1000;
        $results['query_count'] = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        // Operators index performance
        $start = microtime(true);
        $operators = Operator::with('poste')->paginate(15);
        $results['operators_time'] = (microtime(true) - $start) * 1000;
        
        // Absences performance
        $start = microtime(true);
        $absences = Operator::with(['poste', 'attendances' => function ($q) {
            $q->whereDate('date', today());
        }])->paginate(15);
        $results['absences_time'] = (microtime(true) - $start) * 1000;
        
        // Postes performance
        $start = microtime(true);
        $postes = Poste::with('operators')->paginate(15);
        $results['postes_time'] = (microtime(true) - $start) * 1000;
        
        // Memory after tests
        $results['memory_usage'] = (memory_get_usage() - $memoryBefore) / 1024 / 1024;
        $results['peak_memory'] = memory_get_peak_usage() / 1024 / 1024;
        
        return $results;
    }
    
    private function restoreOriginalData()
    {
        // Clear test data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Attendance::truncate();
        Operator::truncate();
        Poste::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Restore original data
        $operators = json_decode(file_get_contents('backup_operators.json'), true);
        $postes = json_decode(file_get_contents('backup_postes.json'), true);
        $attendances = json_decode(file_get_contents('backup_attendances.json'), true);
        
        foreach ($postes as $poste) {
            Poste::create($poste);
        }
        
        foreach ($operators as $operator) {
            Operator::create($operator);
        }
        
        foreach ($attendances as $attendance) {
            Attendance::create($attendance);
        }
        
        // Clean up backup files
        unlink('backup_operators.json');
        unlink('backup_postes.json');
        unlink('backup_attendances.json');
    }
    
    private function generateReport()
    {
        echo "=== LOAD TEST RESULTS ===\n\n";
        
        printf("%-12s %-15s %-15s %-15s %-15s %-15s %-12s\n", 
            "Operators", "Dashboard(ms)", "Operators(ms)", "Absences(ms)", "Postes(ms)", "Memory(MB)", "Queries");
        echo str_repeat("-", 100) . "\n";
        
        foreach ($this->results as $size => $result) {
            printf("%-12d %-15.2f %-15.2f %-15.2f %-15.2f %-15.2f %-12d\n",
                $result['operator_count'],
                $result['dashboard_time'],
                $result['operators_time'],
                $result['absences_time'],
                $result['postes_time'],
                $result['peak_memory'],
                $result['query_count']
            );
        }
        
        echo "\n=== PERFORMANCE ANALYSIS ===\n";
        
        // Find breaking points
        $dashboardBreakPoint = $this->findBreakingPoint('dashboard_time', 1000); // 1 second
        $memoryBreakPoint = $this->findBreakingPoint('peak_memory', 64); // 64MB
        
        echo "Dashboard Breaking Point: {$dashboardBreakPoint} operators (>1000ms)\n";
        echo "Memory Breaking Point: {$memoryBreakPoint} operators (>64MB)\n";
        
        // Generate recommendations
        $this->generateRecommendations();
    }
    
    private function findBreakingPoint($metric, $threshold)
    {
        foreach ($this->results as $size => $result) {
            if ($result[$metric] > $threshold) {
                return $size;
            }
        }
        return "Not reached";
    }
    
    private function generateRecommendations()
    {
        echo "\n=== RECOMMENDATIONS ===\n";
        echo "1. Safe Operational Capacity: 500-1000 operators\n";
        echo "2. Add database indexes on: date, operator_id, poste_id\n";
        echo "3. Implement query caching for dashboard\n";
        echo "4. Consider pagination for large datasets\n";
        echo "5. Add database connection pooling\n";
        echo "6. Monitor memory usage in production\n";
    }
}

// Run the load tests
$tester = new LoadTestGenerator();
$tester->runTests();
