<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Display a listing of the staff members.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $position = $request->input('position');

        $query = Staff::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('staff_id_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('other_names', 'like', "%{$search}%");
            });
        }

        if ($position) {
            $query->where('position', $position);
        }

        $staff = $query->orderBy('first_name')->paginate(15);
        $positions = Staff::distinct()->pluck('position')->toArray();

        $staffCodes = [];
        $usedStaffCodes = [];
        if (auth()->user()->hasRole('Proprietor')) {
            $staffCodes = \App\Models\StaffCode::where('is_used', false)->orderBy('created_at', 'desc')->get();
            $usedStaffCodes = \App\Models\StaffCode::where('is_used', true)->with('usedBy')->orderBy('updated_at', 'desc')->get();
        }

        return view('staff.index', compact('staff', 'positions', 'search', 'position', 'staffCodes', 'usedStaffCodes'));
    }
}
