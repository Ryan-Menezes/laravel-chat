<?php

namespace App\Http\Controllers\Api;

use App\Events\Chat\SendMessage;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    public function index(User $user)
    {
        $userFrom = Auth::user()->id;
        $userTo = $user->id;
        $messages = Message::query()->where([
            'from' => $userFrom,
            'to' => $userTo,
        ])->orWhere([
            'from' => $userTo,
            'to' => $userFrom,
        ])->orderBy('created_at', 'ASC')->get();

        return response()->json(['messages' => $messages], Response::HTTP_OK);
    }

    public function store(Request $request, User $user)
    {
        $message = Message::query()->create([
            'from' => Auth::user()->id,
            'to' => $user->id,
            'content' => $request->input('message'),
        ]);

        Event::dispatch(new SendMessage($user, $message));

        return response()->json(['message' => $message], Response::HTTP_CREATED);
    }
}
