<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first();
        if($setting){
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $setting
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'data not found',
            'data' => null
        ], 404);
    }
}
