<?php

namespace App\Livewire\Apps\Chat;

use App\Models\User;
use Livewire\Component;
use App\Models\ChatMessage;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Sidebar extends Component
{

    public $selectedUser;

    public $searchQuery;


    public function getChats()
    {
        $userId = Auth::id();
        
        // Optimized: Only select needed columns instead of users.*
        $chats = ChatMessage::join('users', function ($join) {
            $join->on('chat_messages.user_id', '=', 'users.id')
                ->orOn('chat_messages.receiver_id', '=', 'users.id');
        })
        ->where(function ($q) use ($userId) {
            $q->where('chat_messages.user_id', $userId)
              ->orWhere('chat_messages.receiver_id', $userId);
        })
        ->where('users.id', '!=', $userId)
        ->select(
            'users.id',
            'users.firstname',
            'users.middlename',
            'users.lastname',
            'users.avatar',
            'users.is_online',
            DB::raw('MAX(chat_messages.created_at) as max_created_at')
        )
        ->orderBy('max_created_at', 'desc')
        ->groupBy('users.id', 'users.firstname', 'users.middlename', 'users.lastname', 'users.avatar', 'users.is_online')
        ->get();
        
        return $chats;
    }



    public function render()
    {
        $query = $this->searchQuery;
        $users = null;
        
        if (!empty($query)) {
            // Optimized: Limit results and only select needed columns
            $users = User::where(function($q) use ($query) {
                $q->where('username', 'LIKE', '%' . $query . '%')
                  ->orWhere('firstname', 'LIKE', '%' . $query . '%')
                  ->orWhere('middlename', 'LIKE', '%' . $query . '%')
                  ->orWhere('lastname', 'LIKE', '%' . $query . '%')
                  ->orWhere('email', 'LIKE', '%' . $query . '%')
                  ->orWhere('phone', 'LIKE', '%' . $query . '%');
            })
            ->select('id', 'firstname', 'middlename', 'lastname', 'avatar', 'is_online', 'email')
            ->limit(20)
            ->get();
        }
        
        $chats = $this->getChats();
        
        return view('livewire.apps.chat.sidebar', compact(
           'users', 'chats'
        ));
    }
}

