<?php

namespace App\Http\Controllers;

use App\Models\StaffCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffCodeController extends Controller
{
    /**
     * Generate a new registration PIN.
     * The PIN persists forever until the principal explicitly replaces it.
     */
    public function generate(Request $request)
    {
        // Generate a unique 6-digit PIN code
        do {
            $code = strval(rand(100000, 999999));
        } while (StaffCode::where('code', $code)->exists());

        StaffCode::create([
            'code' => $code,
            'is_used' => false,
        ]);

        return redirect()->back()->with('success', "Staff registration PIN generated successfully: {$code}");
    }

    /**
     * Regenerate (replace) an existing PIN with a new one.
     * The old PIN is soft-deleted and a new one is created.
     */
    public function regenerate($id)
    {
        $staffCode = StaffCode::findOrFail($id);

        // Generate a new unique code
        do {
            $newCode = strval(rand(100000, 999999));
        } while (StaffCode::where('code', $newCode)->exists());

        // Update the existing record with the new code and reset usage
        $staffCode->update([
            'code' => $newCode,
            'is_used' => false,
            'used_by_user_id' => null,
        ]);

        return redirect()->back()->with('success', "PIN has been regenerated: {$newCode}");
    }

    /**
     * Revoke (delete) an unused PIN.
     * Only the principal can do this.
     */
    public function destroy($id)
    {
        $staffCode = StaffCode::findOrFail($id);

        if ($staffCode->is_used) {
            return redirect()->back()->with('error', 'Cannot revoke a PIN code that has already been used.');
        }

        $staffCode->delete();

        return redirect()->back()->with('success', 'Staff registration PIN revoked successfully.');
    }
}
