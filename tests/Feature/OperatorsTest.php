<?php

namespace Tests\Feature;

use App\Livewire\Operators as OperatorsComponent;
use App\Models\Operator;
use App\Models\Poste;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperatorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_operator_via_modal(): void
    {
        $this->actingAs(User::factory()->create());
        $poste = Poste::factory()->create();

        Livewire::test(OperatorsComponent::class)
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('poste_id', (string)$poste->id)
            ->set('is_capable', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('operators', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'poste_id' => $poste->id,
            'is_capable' => true,
        ]);
    }

    public function test_search_filters_results(): void
    {
        $this->actingAs(User::factory()->create());
        $poste = Poste::factory()->create(['name' => 'Machine A Operator']);
        Operator::factory()->create(['first_name' => 'Alice','last_name' => 'Smith','poste_id' => $poste->id]);
        Operator::factory()->create(['first_name' => 'Bob','last_name' => 'Jones','poste_id' => $poste->id]);

        Livewire::test(OperatorsComponent::class)
            ->set('search', 'Alice')
            ->assertSee('Alice')
            ->assertDontSee('Bob');
    }

    public function test_capability_toggle_updates_db(): void
    {
        $this->actingAs(User::factory()->create());
        $op = Operator::factory()->create(['is_capable' => true, 'poste_id' => Poste::factory()->create()->id]);

        Livewire::test(OperatorsComponent::class)
            ->call('toggleCapability', $op->id);

        $this->assertDatabaseHas('operators', ['id' => $op->id, 'is_capable' => false]);
    }
} 