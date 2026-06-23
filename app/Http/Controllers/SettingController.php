<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    /**
     * Return all settings as a key => value map (admin only).
     */
    public function index()
    {
        Gate::authorize('admin');

        return response()->json([
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    /**
     * Bulk upsert settings (admin only).
     */
    public function update(Request $request)
    {
        Gate::authorize('admin');

        $data = $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'nullable|string|max:2000',
        ]);

        foreach ($data['settings'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return response()->json([
            'message'  => 'Settings saved.',
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }
}
