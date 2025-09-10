<?php

namespace Tests\Feature;

use App\Livewire\Absences as AbsencesComponent;
use App\Models\Attendance;
use App\Models\Operator;
use App\Models\Poste;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AbsencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_absence_toggle_creates_or_updates_record(): void
    {
        $this->actingAs(User::factory()->create());
        $op = Operator::factory()->create(['poste_id' => Poste::factory()->create()->id]);

        Livewire::test(AbsencesComponent::class)
            ->call('toggleAttendance', $op->id);

        $this->assertDatabaseHas('attendances', [
            'operator_id' => $op->id,
            'status' => 'absent',
        ]);

        $this->assertTrue(
            Attendance::where('operator_id', $op->id)
                ->whereDate('date', today())
                ->where('status', 'absent')
                ->exists(),
            'Attendance row for today with status absent was not found.'
        );
    }

    public function test_absences_page_search(): void
    {
        $this->actingAs(User::factory()->create());
        $poste = Poste::factory()->create(['name' => 'Machine A Operator']);
        Operator::factory()->create(['first_name' => 'Claire','last_name' => 'Ray','poste_id' => $poste->id]);
        Operator::factory()->create(['first_name' => 'Dan','last_name' => 'Lee','poste_id' => $poste->id]);

        Livewire::test(AbsencesComponent::class)
            ->set('search', 'Claire')
            ->assertSee('Claire')
            ->assertDontSee('Dan');
    }
} 