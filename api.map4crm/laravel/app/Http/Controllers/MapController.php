<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\Address;
use App\Models\Delivery;

class MapController extends Controller
{
    public function showMap()
    {
        return view('map');
    }

    public function showWorker()
    {
        return view('worker');
    }
    
    public function saveAddress(Request $request)
    {
        try {
            $delivery = new Delivery();
            $delivery->user_id = auth()->id();
            $delivery->address = $request->input('address');
            $delivery->lat = $request->input('lat');
            $delivery->lng = $request->input('lng');
            $delivery->status = $request->input('status'); // Добавлено поле status
            $delivery->save();
    
            return response()->json(['deliveryId' => $delivery->id]);
    
        } catch (\Exception $e) {
            \Log::error('Error saving address: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
    
            return response()->json(['message' => 'Error saving address'], 500);
        }
    }
    
    public function showUserMap($id)
    {
        $delivery = Delivery::find($id);
        return view('user_map', ['delivery' => $delivery]);
    }
    
    public function getUserAddresses()
    {
        $addresses = Delivery::where('user_id', auth()->id())->get();
        return response()->json($addresses);
    }

    public function updateAddressStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:in_process,on_the_way,received',
        ]);

        $address = Delivery::find($id);
        $address->status = $request->status;
        $address->save();

        return response()->json(['success' => true, 'address' => $address]);
    }

    public function confirmOrder(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);
        
        $delivery = new Delivery();
        $delivery->latitude = $request->lat;
        $delivery->longitude = $request->lng;
        $delivery->user_id = auth()->id(); 
        $delivery->status = $request->input('status'); // Добавлено поле status
        $delivery->save();

        $userAddress = Address::where('user_id', auth()->id())->first();

        return response()->json([
            'success' => true,
            'deliveryId' => $delivery->id,
            'userLocation' => [
                'lat' => $userAddress->latitude,
                'lng' => $userAddress->longitude
            ]
        ]);
    }

    public function getDeliveryLocation($id)
    {
        $delivery = Delivery::find($id);

        return response()->json([
            'lat' => $delivery->latitude,
            'lng' => $delivery->longitude
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
            'lng' => $userAddress->longitude
        ]);
    }

    public function streamOrders()
    {
        return response()->stream(function () {
            while (true) {
                $orders = Delivery::where('user_id', auth()->id())->get();
                echo "data: " . json_encode($orders) . "\n\n";
                ob_flush();
                flush();
                sleep(5); 
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:in_process,on_the_way,received',
        ]);

        $delivery = Delivery::find($id);
        $delivery->status = $request->status;
        $delivery->save();

        return response()->json(['success' => true, 'delivery' => $delivery]);
    }

    public function saveCourierCoordinates(Request $request)
    {
        $request->validate([
            'orderId' => 'required|integer',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);
    
        $delivery = Delivery::find($request->orderId);
        $delivery->lat = $request->lat;
        $delivery->lng = $request->lng;
        $delivery->save();
    
        return response()->json(['success' => true, 'delivery' => $delivery]);
    }
    
    public function getDeliveryCoordinates($id)
    {
        $delivery = Delivery::find($id);
        return response()->json(['latitude' => $delivery->lat, 'longitude' => $delivery->lng]);
    }
}    