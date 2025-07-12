<?php
// app/Http/Controllers/ChatController.php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatController extends Controller
{
    // Get current authenticated user info
    public function getCurrentUser()
    {
        $user = Auth::user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'online_status' => $user->is_online ? 'online' : 'offline',
            'last_seen' => $user->last_seen,
        ]);
    }

    // Update user presence (online status)
    public function updatePresence()
    {
        $user = Auth::user();
        $user->update([
            'is_online' => true,
            'last_seen' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Presence updated successfully']);
    }

    // Get all users except current user (for starting new conversations)
    public function getUsers()
    {
        $users = User::where('id', '!=', Auth::id())
                    ->get(['id', 'name', 'email', 'is_online', 'last_seen'])
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'is_online' => $user->is_online,
                            'online_status' => $user->is_online ? 'online' : 'offline',
                            'last_seen' => $user->last_seen,
                        ];
                    });

        return response()->json($users);
    }

    // Get all conversations for authenticated user
    public function getConversations()
    {
        $user = Auth::user();
        $conversations = $user->conversations()
            ->with(['participants' => function($query) {
                $query->where('user_id', '!=', Auth::id());
            }, 'latestMessage.user'])
            ->withCount(['messages as unread_count' => function($query) {
                $query->where('user_id', '!=', Auth::id())
                      ->where('read_at', null);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($conversations);
    }

    // Get a specific conversation
    public function getConversation($conversationId)
    {
        $conversation = Conversation::with(['participants', 'messages.user'])
            ->whereHas('participants', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->findOrFail($conversationId);

        return response()->json($conversation);
    }

    // Start a new conversation with a user
    public function startConversation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $otherUser = User::find($request->user_id);

        // Check if conversation already exists between these users
        $existingConversation = Conversation::whereHas('participants', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->whereHas('participants', function ($query) use ($otherUser) {
            $query->where('user_id', $otherUser->id);
        })->whereHas('participants', function ($query) {
            $query->havingRaw('COUNT(*) = 2');
        })->first();

        if ($existingConversation) {
            return response()->json([
                'conversation' => $existingConversation->load(['participants', 'messages.user']),
                'exists' => true
            ]);
        }

        // Create new conversation
        $conversation = Conversation::create([
            'name' => $currentUser->name . ' & ' . $otherUser->name
        ]);

        // Add participants
        $conversation->participants()->attach([
            $currentUser->id => ['joined_at' => Carbon::now()],
            $otherUser->id => ['joined_at' => Carbon::now()]
        ]);

        return response()->json([
            'conversation' => $conversation->load(['participants', 'messages.user']),
            'exists' => false
        ]);
    }

    // Get messages for a specific conversation
    public function getMessages($conversationId)
    {
        $conversation = Conversation::whereHas('participants', function ($query) {
            $query->where('user_id', Auth::id());
        })->findOrFail($conversationId);

        $messages = Message::where('conversation_id', $conversationId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Message::where('conversation_id', $conversationId)
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return response()->json($messages);
    }

    // Send a message
    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $conversation = Conversation::whereHas('participants', function ($query) {
            $query->where('user_id', Auth::id());
        })->findOrFail($conversationId);

        $message = Message::create([
            'conversation_id' => $conversationId,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        // Update conversation timestamp
        $conversation->touch();

        // Load the user relationship
        $message->load('user');

        return response()->json($message);
    }

    // Mark conversation messages as read
    public function markAsRead($conversationId)
    {
        $conversation = Conversation::whereHas('participants', function ($query) {
            $query->where('user_id', Auth::id());
        })->findOrFail($conversationId);

        Message::where('conversation_id', $conversationId)
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return response()->json(['message' => 'Messages marked as read']);
    }

    // Delete a conversation
    public function deleteConversation($conversationId)
    {
        $conversation = Conversation::whereHas('participants', function ($query) {
            $query->where('user_id', Auth::id());
        })->findOrFail($conversationId);

        // Remove the current user from the conversation
        $conversation->participants()->detach(Auth::id());

        // If no participants left, delete the conversation entirely
        if ($conversation->participants()->count() === 0) {
            $conversation->delete();
        }

        return response()->json(['message' => 'Conversation deleted successfully']);
    }
}
