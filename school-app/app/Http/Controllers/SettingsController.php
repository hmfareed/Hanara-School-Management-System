<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => ['required', 'array'],
            'settings.school_name' => ['sometimes', 'string', 'max:255'],
            'settings.school_motto' => ['sometimes', 'string', 'max:500'],
            'settings.school_address' => ['sometimes', 'string', 'max:500'],
            'settings.school_phone' => ['sometimes', 'string', 'max:20'],
            'settings.school_email' => ['sometimes', 'email', 'max:255'],
            'settings.ca_weight' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'settings.exam_weight' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'settings.bece_at_risk_threshold' => ['sometimes', 'integer', 'min:6', 'max:54'],
        ]);

        $settingsData = $request->input('settings', []);

        // Validate CA + Exam weights sum to 100
        if (isset($settingsData['ca_weight']) && isset($settingsData['exam_weight'])) {
            $sum = (float)$settingsData['ca_weight'] + (float)$settingsData['exam_weight'];
            if (abs($sum - 100) > 0.01) {
                return back()->withErrors(['settings.ca_weight' => 'CA weight + Exam weight must equal 100.'])->withInput();
            }
        }

        foreach ($settingsData as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $oldValue = $setting->value;
                $setting->update(['value' => $value]);

                if ($oldValue !== $value) {
                    AuditLog::log('updated', $setting, ['value' => $oldValue], ['value' => $value]);
                }
            } else {
                Setting::set($key, $value);
            }

            // Sync database tables with active academic state
            if ($key === 'current_academic_year_id') {
                \App\Models\AcademicYear::query()->update(['is_current' => false]);
                \App\Models\AcademicYear::where('id', $value)->update(['is_current' => true]);
            }
            if ($key === 'current_term_id') {
                \App\Models\Term::query()->update(['is_current' => false]);
                \App\Models\Term::where('id', $value)->update(['is_current' => true]);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
