<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function saveLocation(Request $request)
    {
        $data = $request->all();
        // Здесь вы можете сохранить данные в базу данных или обработать их другим образом
        return response()->json(['success' => true, 'data' => $data]);
    }
}
