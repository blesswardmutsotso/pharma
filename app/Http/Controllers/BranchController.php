<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::orderByDesc('is_home')->orderBy('name')->get();
        return view('admin.branches', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'code'    => 'required|string|max:20|unique:branches,code',
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:30',
        ]);

        Branch::create(array_merge($data, ['is_active' => true, 'is_home' => false]));

        return back()->with('success', 'Branch created.');
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'code'      => 'required|string|max:20|unique:branches,code,' . $branch->id,
            'address'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:30',
            'is_active' => 'boolean',
        ]);

        $branch->update($data);

        return back()->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->is_home) {
            return back()->with('error', 'Cannot delete the home branch.');
        }

        $branch->delete();

        return back()->with('success', 'Branch deleted.');
    }
}
