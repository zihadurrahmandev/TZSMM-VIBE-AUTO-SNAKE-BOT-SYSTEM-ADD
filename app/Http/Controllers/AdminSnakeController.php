<?php

namespace App\Http\Controllers;

use App\Models\GameTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

class AdminSnakeController extends Controller
{

    public function index()
    {
        $game_types = GameTypes::where('game', 'snake')
            ->orderByRaw('CAST(joining AS DECIMAL(10,2)) ASC')->get();
        $snake_status = Setting::where('key', 'snake_status')->value('value') ?? 'off';
        $snake_logo_url = Setting::where('key', 'snake_logo')->value('value') ?? null;
        $snake_subtitle = Setting::where('key', 'snake_subtitle')->value('value') ?? '';

        return view('admin.settings.snake', compact('game_types', 'snake_status', 'snake_logo_url', 'snake_subtitle'));
    }
public function updatesnakeSettings(Request $request)
{
    $request->validate([
        'snake_status' => 'required|in:on,off',
        'snake_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'snake_subtitle' => 'nullable|string|max:255',
        'snake_minimum' => 'nullable|numeric|min:1',
        'snake_winning' => 'nullable|numeric|min:0.01',
        'snake_bot_status' => 'required|in:on,off',
        'snake_bot_winning' => 'nullable|numeric|min:0.01|max:100',
    ]);

    Setting::updateOrCreate(['key' => 'snake_status'], ['value' => $request->snake_status]);

    if ($request->hasFile('snake_logo')) {
        $oldLogo = Setting::where('key', 'snake_logo')->value('value');
        if ($oldLogo && file_exists(public_path($oldLogo))) {
            unlink(public_path($oldLogo));
        }

        $destinationDir = public_path('logo');
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $file = $request->file('snake_logo');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move($destinationDir, $filename);
        $path = 'logo/' . $filename;

        Setting::updateOrCreate(['key' => 'snake_logo'], ['value' => $path]);
    }

    Setting::updateOrCreate(['key' => 'snake_subtitle'], ['value' => $request->snake_subtitle ?? '']);
    Setting::updateOrCreate(['key' => 'snake_minimum'], ['value' => $request->snake_minimum ?? 30]);
    Setting::updateOrCreate(['key' => 'snake_winning'], ['value' => $request->snake_winning ?? 1.8]);

    // 🔄 Bot-specific settings
    Setting::updateOrCreate(['key' => 'snake_bot_status'], ['value' => $request->snake_bot_status ?? 'on']);
    Setting::updateOrCreate(['key' => 'snake_bot_winning'], ['value' => $request->snake_bot_winning ?? 70]);

    return redirect()->route('admin.snake-settings')->with('success', 'Snake settings updated successfully');
}




}
