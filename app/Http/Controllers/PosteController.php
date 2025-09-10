<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePosteRequest;
use App\Http\Requests\UpdatePosteRequest;
use App\Models\Poste;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosteController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->input('search', '');

        $postes = Poste::query()
            ->with('operators')
            ->when($search !== '', function ($q) use ($search) {
                $term = '%'.$search.'%';
                $q->where('name', 'like', $term)
                  ->orWhereHas('operators', function ($operatorQuery) use ($term) {
                      $operatorQuery->where('first_name', 'like', $term)
                                   ->orWhere('last_name', 'like', $term)
                                   ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$term]);
                  });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $total = Poste::count();

        return view('postes.index', compact('postes', 'search', 'total'));
    }

    public function create(): View
    {
        return view('postes.create');
    }

    public function store(StorePosteRequest $request): RedirectResponse
    {
        Poste::create($request->validated());
        return redirect()->route('postes.index')->with('success', 'Success! Poste created.');
    }

    public function edit(Poste $poste): View
    {
        return view('postes.edit', compact('poste'));
    }

    public function update(UpdatePosteRequest $request, Poste $poste): RedirectResponse
    {
        $poste->update($request->validated());
        return redirect()->route('postes.index')->with('success', 'Success! Poste updated.');
    }

    public function destroy(Poste $poste): RedirectResponse
    {
        $poste->delete();
        return redirect()->route('postes.index')->with('success', 'Poste deleted successfully.');
    }
}
