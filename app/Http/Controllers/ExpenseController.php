<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Profile;
use App\Models\Expense;

class ExpenseController extends Controller
{
   
public function store(Request $request)
{
    
    $validated = $request->validate([
        'date' => 'required|date',
        'description' => 'required|string',
        'amount' => 'required|numeric',
        'notes' => 'nullable|string|max:255',
        'attachment' => 'nullable|file|mimes:jpeg,png,pdf,docx|max:10240', 
    ]);
    $validated['user_id'] = Auth::id();
    if ($request->hasFile('attachment')) {
        $validated['attachment'] = $request->file('attachment')->store('expenses', 'public');
    }
    Expense::create($validated);
    return redirect()->back()->with('success', 'Expense added successfully.');
}

public function index()
{
    $expenses = Expense::where('user_id', Auth::id())->get();
    return view('expenses.index', compact('expenses'));
}

public function edit($id)
{
    $expense = Expense::findOrFail($id);
    return view('expenses.edit', compact('expense'));
}

public function show($id)
{
    $expense = Expense::findOrFail($id);
    return view('expenses.show', compact('expense'));
}

}