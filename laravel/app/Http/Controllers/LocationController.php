<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function saveLocation(Request $request)
    {
        $data = $request->all();
        return response()->json(['success' => true, 'data' => $data]);
    }
}
