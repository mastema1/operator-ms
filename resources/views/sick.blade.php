<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Therapy Schedule</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .schedule-table {
            border-collapse: collapse;
            width: 100%;
        }
        .schedule-table th,
        .schedule-table td {
            border: 1px solid #e5e7eb;
            padding: 16px;
            text-align: left;
            vertical-align: top;
        }
        .schedule-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        .day-cell {
            background-color: #f3f4f6;
            font-weight: 600;
            color: #1f2937;
            width: 120px;
        }
        .morning-session {
            background-color: #fef3c7;
        }
        .afternoon-session {
            background-color: #dbeafe;
        }
        .exercise-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .exercise-content {
            line-height: 1.6;
        }
        .exercise-content ul {
            margin: 8px 0;
            padding-left: 20px;
        }
        .exercise-content li {
            margin: 4px 0;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4">
                <h1 class="text-2xl font-bold">Physical Therapy Schedule</h1>
                <p class="text-blue-100 mt-1">Weekly Exercise Program</p>
            </div>
            
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th class="day-cell">Day</th>
                                <th class="morning-session">Morning Session (45-60 mins after medication)</th>
                                <th class="afternoon-session">Afternoon Session (Lighter Activity)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="day-cell">Monday</td>
                                <td class="morning-session">
                                    <div class="exercise-title">PT Day 1: LSVT BIG & Gait Focus (60 mins)</div>
                                    <div class="exercise-content">
                                        • <strong>Warm-up (5 mins):</strong> Gentle marching, arm circles.<br>
                                        • <strong>LSVT BIG Maximal Daily Exercises:</strong> Floor-to-ceiling reaches, side-to-side reaches, forward/sideways/backward steps and reaches (perform all with BIG effort).<br>
                                        • <strong>Gait Training:</strong> Practice walking with visual cues (tape on floor) and auditory cues (metronome). Focus on long strides and big arm swings.<br>
                                        • <strong>Functional Tasks:</strong> Practice sit-to-stands from different chairs.
                                    </div>
                                </td>
                                <td class="afternoon-session">
                                    <div class="exercise-title">Daily Exercises (20 mins)</div>
                                    <div class="exercise-content">
                                        • <strong>Balance:</strong> Tandem Stance (30 sec each side).<br>
                                        • <strong>Stretching:</strong> Trunk Twists (3 each side), Chair Thoracic Extension (3 reps).
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="day-cell">Tuesday</td>
                                <td class="morning-session">
                                    <div class="exercise-title">PT Day 2: Balance & Turning Focus (60 mins)</div>
                                    <div class="exercise-content">
                                        • <strong>Warm-up (5 mins):</strong> Exaggerated Marching.<br>
                                        • <strong>LSVT BIG Maximal Daily Exercises.</strong><br>
                                        • <strong>Balance & Turning:</strong> Set up a small obstacle course with pillows. Practice "Clock Turns" and wide "U-Turns" to avoid pivoting.<br>
                                        • <strong>Functional Tasks:</strong> Practice walking while carrying a safe object (e.g., a plastic cup on a tray).
                                    </div>
                                </td>
                                <td class="afternoon-session">
                                    <div class="exercise-title">Daily Exercises (20 mins)</div>
                                    <div class="exercise-content">
                                        • <strong>Strength:</strong> Sit-to-Stands (2 sets of 10).<br>
                                        • <strong>Stretching:</strong> Trunk Twists & Chair Thoracic Extension.
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="day-cell">Wednesday</td>
                                <td class="morning-session">
                                    <div class="exercise-title">Active Recovery Day (30-40 mins)</div>
                                    <div class="exercise-content">
                                        • <strong>Warm-up (5 mins):</strong> Rocking in a chair.<br>
                                        • <strong>Daily Exercise Full Circuit:</strong><br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;1. Exaggerated Marching (2 mins).<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;2. Weight Shifting (10 each direction).<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;3. Sit-to-Stands (2 sets of 10).<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;4. Tandem Stance (30 sec each side).<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;5. Trunk Twists (3 each side).<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;6. Chair Thoracic Extension (3 reps).
                                    </div>
                                </td>
                                <td class="afternoon-session">
                                    <div class="exercise-title">Leisure Walk (15-20 mins)</div>
                                    <div class="exercise-content">
                                        • Focus on enjoying the walk. Use a walker or cane if needed for stability. Focus on stride length.
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="day-cell">Thursday</td>
                                <td class="morning-session">
                                    <div class="exercise-title">PT Day 3: LSVT BIG & Dual-Tasking (60 mins)</div>
                                    <div class="exercise-content">
                                        • <strong>Warm-up (5 mins):</strong> Gentle marching, arm circles.<br>
                                        • <strong>LSVT BIG Maximal Daily Exercises.</strong><br>
                                        • <strong>Dual-Task Training:</strong> Practice walking in a safe space while counting backward from 50 or naming animals.<br>
                                        • <strong>Functional Tasks:</strong> Practice getting in and out of a car.
                                    </div>
                                </td>
                                <td class="afternoon-session">
                                    <div class="exercise-title">Daily Exercises (20 mins)</div>
                                    <div class="exercise-content">
                                        • <strong>Balance:</strong> Tandem Stance (30 sec each side).<br>
                                        • <strong>Stretching:</strong> Trunk Twists & Chair Thoracic Extension.
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="day-cell">Friday</td>
                                <td class="morning-session">
                                    <div class="exercise-title">PT Day 4: Full Integration (60 mins)</div>
                                    <div class="exercise-content">
                                        • <strong>Warm-up (5 mins):</strong> Exaggerated Marching.<br>
                                        • <strong>LSVT BIG Maximal Daily Exercises.</strong><br>
                                        • <strong>Hierarchy Task:</strong> Combine movements. Example: Stand up from a chair, walk 15 feet, navigate around a cone (pillow), and sit back down.<br>
                                        • <strong>Patient's Choice:</strong> Work on one specific movement or task he finds most challenging.
                                    </div>
                                </td>
                                <td class="afternoon-session">
                                    <div class="exercise-title">Daily Exercises (20 mins)</div>
                                    <div class="exercise-content">
                                        • <strong>Strength:</strong> Sit-to-Stands (2 sets of 10).<br>
                                        • <strong>Stretching:</strong> Trunk Twists & Chair Thoracic Extension.
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="day-cell">Saturday</td>
                                <td class="morning-session">
                                    <div class="exercise-title">Active Recovery Day (30-40 mins)</div>
                                    <div class="exercise-content">
                                        • <strong>Warm-up (5 mins):</strong> Rocking in a chair.<br>
                                        • <strong>Daily Exercise Full Circuit:</strong><br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;1. Exaggerated Marching (2 mins).<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;2. Weight Shifting (10 each direction).<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;3. Sit-to-Stands (2 sets of 10).<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;4. Tandem Stance (30 sec each side).<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;5. Trunk Twists (3 each side).<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;6. Chair Thoracic Extension (3 reps).
                                    </div>
                                </td>
                                <td class="afternoon-session">
                                    <div class="exercise-title">Stretching & Flexibility Focus</div>
                                    <div class="exercise-content">
                                        • Spend extra time on the Trunk Twists and Chair Thoracic Extension, holding each stretch a little longer.
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Important Notes:</h3>
                    <ul class="text-blue-700 space-y-1">
                        <li>• All exercises should be performed 45-60 minutes after medication for optimal effectiveness</li>
                        <li>• Focus on "BIG" movements - amplitude is key for neuroplasticity</li>
                        <li>• Use safety equipment (walker, cane) as needed</li>
                        <li>• Stop if experiencing dizziness or unusual fatigue</li>
                        <li>• Sunday is a complete rest day</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
