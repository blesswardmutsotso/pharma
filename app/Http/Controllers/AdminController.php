<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class AdminController extends Controller
{



    public function countUsers()
{
    $userCount = User::where('user_type', 0)->count();
    return view('admin.dashboard', compact('userCount'));
}



public function showUserManagement()
{
    $users = User::all(); // Fetch all users from the database
    return view('admin.dashboard', compact('users')); // Pass users to the view
}
    
    public function index()
    {
        $users = User::all(); 
        
       
     
        return view('admin.dashboard', compact( 'users'));

    }

    public function dashboard()
    {
        $users = User::all(); 
    
        $total_profit = Product::sum(DB::raw('(selling_price - cost_price) * stock'));
        return view('admin.dashboard', compact('total_profit','productss','users'));
    }
    public function manageUsers()
    {
        $users = \App\Models\User::all();
        return view('admin.dashboard', compact('users'));
    }

   
    public function settings()
    {
        return view('admin.settings');
    }

    public function destroy($id)
    {
        // Ensure only admin can delete
        if (!auth()->user()->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['status' => 'success', 'message' => 'User deleted successfully']);
    }

    public function resetPassword(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'new_password' => 'required|min:6|confirmed',
        ]);
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['status' => 'success', 'message' => 'Password reset successfully']);
    }

}

