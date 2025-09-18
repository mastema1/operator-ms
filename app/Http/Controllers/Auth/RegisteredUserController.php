<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Poste;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($request, &$user) {
            // Create a new tenant for this user
            $tenant = Tenant::create([
                'name' => $request->name . "'s Organization",
            ]);

            // Create the user and associate with the tenant
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tenant_id' => $tenant->id,
            ]);

            // Create required postes for the new tenant
            $this->createPostesForTenant($tenant->id);
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Create required postes for a new tenant.
     */
    private function createPostesForTenant(int $tenantId): void
    {
        // Create Poste 1 through Poste 40
        for ($i = 1; $i <= 40; $i++) {
            Poste::create([
                'name' => 'Poste ' . $i,
                'is_critical' => false,
                'tenant_id' => $tenantId,
            ]);
        }

        // Create additional specific postes
        $specificPostes = [
            'ABS',
            'Bol',
            'Bouchon',
            'CMC',
            'COND',
            'FILISTE',
            'FILISTE EPS',
            'FW',
            'Polyvalent',
            'Ravitailleur',
            'Retouche',
            'TAG',
            'Team Speaker',
            'VISSEUSE'
        ];

        foreach ($specificPostes as $posteName) {
            Poste::create([
                'name' => $posteName,
                'is_critical' => false,
                'tenant_id' => $tenantId,
            ]);
        }
    }
}
