<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Delivery;

class MapController extends Controller
{
    public function showMap()
    {
        return view('map');
    }

    public function showTracker()
    {
        return view('tracker');
    }
    
    public function saveAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);
    
        $data = [
            'address' => $request->address,
            'latitude' => $request->lat,
            'longitude' => $request->lon,
        ];
    
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        $filePath = storage_path('app/address.json');
    
        \Log::info('Saving address to JSON file', ['filePath' => $filePath, 'data' => $data]);
    
        if (file_put_contents($filePath, $jsonData) === false) {
            \Log::error('Failed to write to JSON file', ['filePath' => $filePath]);
            return response()->json(['success' => false, 'message' => 'Failed to save address'], 500);
        }
    
        \Log::info('Address saved successfully', ['filePath' => $filePath]);
    
        return response()->json(['success' => true, 'address' => $data]);
    }    
    

    public function confirmOrder(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);
        
        $delivery = new Delivery();
        $delivery->latitude = $request->lat;
        $delivery->longitude = $request->lon;
        $delivery->user_id = auth()->id(); 
        $delivery->save();

        $userAddress = Address::where('user_id', auth()->id())->first();

        return response()->json([
            'success' => true,
            'deliveryId' => $delivery->id,
            'userLocation' => [
                'lat' => $userAddress->latitude,
                'lon' => $userAddress->longitude
            ]
        ]);
    }

    public function getDeliveryLocation($id)
    {
        $delivery = Delivery::find($id);

        return response()->json([
            'lat' => $delivery->latitude,
            'lon' => $delivery->longitude
        ]);
    }

    public function getUserLocation()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userAddress = Address::where('user_id', auth()->id())->first();

        if (!$userAddress) {
            return response()->json(['error' => 'User address not found'], 404);
        }

        return response()->json([
            'lat' => $userAddress->latitude,
            'lon' => $userAddress->longitude
        ]);
    }
}
