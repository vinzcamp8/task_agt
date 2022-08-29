<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

use App\Events\MessageSent;

class ChatsController extends Controller
{
    //Add the below functions
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('chat');
    }

    public function fetchMessages()
    {
        $allmex = Message::with('user')->get(); // $allmex è una collezione con tutti i dati di user e relativi messaggi
        foreach ($allmex as &$item) {
            try{
                $item["message"] = Crypt::decryptString($item["message"]); 
            } catch (DecryptException $e) {
                $item["message"] = "Non è stato possibile recuperare il messaggio!";
            }
        }
        return $allmex;
    }



    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        $message = $user->messages()->create([
            'message' => Crypt::encryptString($request->input('message'))
        ]);        
        //broadcast(new MessageSent($user, $message))->toOthers();
        broadcast(new MessageSent())->toOthers();
        return ['status' => 'Message Sent!'];
    }
}
