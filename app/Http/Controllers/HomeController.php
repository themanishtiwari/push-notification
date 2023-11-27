<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function saveToken(Request $request)
    {
        auth()->user()->update(['device_token'=>$request->token]);
        return response()->json(['token saved successfully.']);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function sendNotification(Request $request)
    {
        $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();
        // $firebaseToken=array("e46r2QuZ3GazP217DZ8nq-:APA91bGquzKKwByn7AH06sfNMFM44AqOtKm41p0LS_5JoKwoLm9P3O4GHnMZeKIYapEfY6MIJcb1KugXys6OU5ddaCKZn-OVSQsVSSnXDsfED3HnzZFT6G8vECjYy4OIzWrcJN_IhqW3");

        $SERVER_API_KEY = 'AAAA3gF-Yqk:APA91bHqTk_8E7GkqKkzWAjvJN7jlodw53qxEMKmcNH8Oio5HOQAAmnU8j2NDTgULPQe1GwRHB3_3a1CCGdLlb-ugnXawxOmLMuQu3J54ZZV2GgH8R28C95ai7XHN9QD6C_BHypMCXgX';

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,
                "content_available" => true,
                "priority" => "high",
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        dd($response);
    }
}
