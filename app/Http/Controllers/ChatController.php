<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Events\NewChatMessage;
use App\Models\Message;

class ChatController extends Controller
{
    /**
     * Retrieve the list of all chat rooms.
     *
     * @param Request $request The incoming request.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function roomsList(Request $request)
    {
        return ChatRoom::orderBy('broadcast_date', 'DESC')
            ->withCount('messages')
            ->get();
    }

    /**
     * Retrieve the list of all chat rooms.
     *
     * @param Request $request The incoming request.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function rooms(Request $request)
    {
        return ChatRoom::orderBy('on_air', 'DESC')
            ->orderBy('broadcast_date', 'DESC')
            ->orderBy('closed', 'ASC')
            ->withCount('messages')
            ->get();
    }

    /**
     * Retrieve a specific chat room.
     *
     * @param Request $request The incoming request.
     * @param int $roomId The ID of the chat room.
     * @return ChatRoom
     */
    public function room(Request $request, $roomId)
    {
        return ChatRoom::where('id', $roomId)->first();
    }

    /**
     * Retrieve all chat messages for a specific room.
     *
     * @param Request $request The incoming request.
     * @param int $roomId The ID of the chat room.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function messages(Request $request, $roomId)
    {
        $messages = ChatMessage::where('chat_room_id', $roomId)
            ->with(['text', 'audio'])
            ->whereIn('status', [1, 2, 3, 4, 5])
            ->orderBy('created_at', 'DESC')
            ->get();

        return $messages;
    }

    /**
     * Create a new chat message for a specific room.
     *
     * @param Request $request The incoming request.
     * @param int $roomId The ID of the chat room.
     * @return Message
     */
    public function newMessage(Request $request, $roomId)
    {
        $newChatMessage = new ChatMessage();
        $newChatMessage->content =  $request->message;
        $newChatMessage->chat_room_id = $roomId;
        $newChatMessage->save();

        event(new NewChatMessage($newChatMessage));

        $newMessage = new Message();
        $newMessage->chat_message_id = $newChatMessage->id;
        $newMessage->save();

        return $newMessage;
    }
    /**
     * Retrieve text messages for a specific chat room.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $roomId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function textMessages(Request $request, $roomId)
    {
        $messages = ChatMessage::where('chat_room_id', $roomId)
            ->with(['text', 'audio'])
            ->whereIn('status', [1, 2, 3, 4, 5])
            ->leftJoin('phone_calls', 'chat_messages.id', '=', 'phone_calls.chat_message_id')
            ->select('chat_messages.*')
            ->whereNull('phone_calls.chat_message_id')
            ->orderBy('created_at', 'DESC')
            ->get();

        return $messages;
    }
}
