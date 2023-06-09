<?php

namespace App\Events;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;

class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatMessage;

    /**
     * Create a new event instance.
     *
     * @param  ChatMessage  $chatMessage
     * @return void
     */
    public function __construct(ChatMessage $chatMessage)
    {
        $this->chatMessage = $chatMessage;
    }
    public function broadcastOn()
    {
        return new Channel('chat.' . $this->chatMessage->chat_room_id);
    }
    
    public function broadcastAs()
    {
        return 'message.new';
    }
}
