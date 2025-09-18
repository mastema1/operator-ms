<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Operator;
use App\Models\Poste;
use App\Models\Attendance;
use App\Models\BackupAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_only_see_their_tenant_data()
    {
        // Create two tenants
        $tenant1 = Tenant::create(['name' => 'Tenant 1']);
        $tenant2 = Tenant::create(['name' => 'Tenant 2']);

        // Create users for each tenant
        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        // Create operators for each tenant
        $operator1 = Operator::factory()->create(['tenant_id' => $tenant1->id]);
        $operator2 = Operator::factory()->create(['tenant_id' => $tenant2->id]);

        // Create postes for each tenant
        $poste1 = Poste::factory()->create(['tenant_id' => $tenant1->id]);
        $poste2 = Poste::factory()->create(['tenant_id' => $tenant2->id]);

        // Test as user 1 - should only see tenant 1 data
        $this->actingAs($user1);
        
        $this->assertEquals(1, Operator::count());
        $this->assertEquals($operator1->id, Operator::first()->id);
        
        $this->assertEquals(1, Poste::count());
        $this->assertEquals($poste1->id, Poste::first()->id);

        // Test as user 2 - should only see tenant 2 data
        $this->actingAs($user2);
        
        $this->assertEquals(1, Operator::count());
        $this->assertEquals($operator2->id, Operator::first()->id);
        
        $this->assertEquals(1, Poste::count());
        $this->assertEquals($poste2->id, Poste::first()->id);
    }

    public function test_tenant_scope_prevents_cross_tenant_access()
    {
        // Create two tenants with data
        $tenant1 = Tenant::create(['name' => 'Tenant 1']);
        $tenant2 = Tenant::create(['name' => 'Tenant 2']);

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        $operator1 = Operator::factory()->create(['tenant_id' => $tenant1->id]);
        $operator2 = Operator::factory()->create(['tenant_id' => $tenant2->id]);

        // Acting as user 1, try to access operator 2 directly
        $this->actingAs($user1);
        
        // This should return null because of tenant scoping
        $this->assertNull(Operator::find($operator2->id));
        
        // But should be able to access operator 1
        $this->assertNotNull(Operator::find($operator1->id));

        // Switch to user 2
        $this->actingAs($user2);
        
        // Now should be able to access operator 2 but not operator 1
        $this->assertNotNull(Operator::find($operator2->id));
        $this->assertNull(Operator::find($operator1->id));
    }

    public function test_new_records_automatically_get_tenant_id()
    {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user);

        // Create a new operator - should automatically get the user's tenant_id
        $operator = Operator::factory()->create();
        
        $this->assertEquals($tenant->id, $operator->tenant_id);
    }
}
