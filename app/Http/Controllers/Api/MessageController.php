<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
    // Send a message
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_id' => 'required|exists:users,id',
            'property_id' => 'nullable|exists:properties,id',
            'content' => 'required|string',
            'message_type' => 'string|in:general,property_inquiry,roommate_inquiry',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Prevent sending message to self
        if ($request->recipient_id == $request->user()->id) {
            return response()->json(['error' => 'Cannot send message to yourself'], 400);
        }

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $request->recipient_id,
            'property_id' => $request->property_id,
            'content' => $request->content,
            'message_type' => $request->message_type ?? 'general',
            'is_read' => false,
        ]);

        $message->load(['sender', 'recipient', 'property']);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message,
        ], 201);
    }

    // Get conversations (list of users with recent messages)
    public function conversations(Request $request)
    {
        $user = $request->user();
        
        // Get unique conversations
        $conversations = Message::select('sender_id', 'recipient_id')
            ->where(function($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->groupBy('sender_id', 'recipient_id')
            ->get()
            ->map(function($msg) use ($user) {
                $otherUserId = $msg->sender_id == $user->id ? $msg->recipient_id : $msg->sender_id;
                return $otherUserId;
            })
            ->unique();

        $conversationUsers = User::whereIn('id', $conversations)
            ->select('id', 'name', 'email', 'profile_image')
            ->get();

        // Get last message for each conversation
        $conversationsWithLastMessage = $conversationUsers->map(function($otherUser) use ($user) {
            $lastMessage = Message::where(function($query) use ($user, $otherUser) {
                $query->where('sender_id', $user->id)
                      ->where('recipient_id', $otherUser->id)
                      ->orWhere('sender_id', $otherUser->id)
                      ->where('recipient_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->first();

            return [
                'user' => $otherUser,
                'last_message' => $lastMessage,
                'unread_count' => Message::where('sender_id', $otherUser->id)
                    ->where('recipient_id', $user->id)
                    ->where('is_read', false)
                    ->count(),
            ];
        });

        return response()->json(['conversations' => $conversationsWithLastMessage]);
    }

    // Get messages between current user and another user
    public function conversation(Request $request, $userId)
    {
        $user = $request->user();
        
        // Verify the other user exists
        $otherUser = User::find($userId);
        if (!$otherUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $messages = Message::where(function($query) use ($user, $userId) {
            $query->where('sender_id', $user->id)
                  ->where('recipient_id', $userId)
                  ->orWhere('sender_id', $userId)
                  ->where('recipient_id', $user->id);
        })
        ->with(['sender:id,name,profile_image', 'recipient:id,name,profile_image', 'property:id,title'])
        ->orderBy('created_at', 'asc')
        ->paginate($request->get('per_page', 20));

        // Mark messages as read
        Message::where('sender_id', $userId)
               ->where('recipient_id', $user->id)
               ->where('is_read', false)
               ->update(['is_read' => true]);

        return response()->json([
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ]
        ]);
    }

    // Get unread messages count
    public function unreadCount(Request $request)
    {
        $count = Message::where('recipient_id', $request->user()->id)
                       ->where('is_read', false)
                       ->count();

        return response()->json(['unread_count' => $count]);
    }

    // Mark message as read
    public function markAsRead(Request $request, $messageId)
    {
        $message = Message::where('id', $messageId)
                         ->where('recipient_id', $request->user()->id)
                         ->first();

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $message->update(['is_read' => true]);

        return response()->json(['message' => 'Message marked as read']);
    }

    // Mark all messages from a user as read
    public function markAllAsRead(Request $request, $userId)
    {
        $updated = Message::where('sender_id', $userId)
                         ->where('recipient_id', $request->user()->id)
                         ->where('is_read', false)
                         ->update(['is_read' => true]);

        return response()->json([
            'message' => 'Messages marked as read',
            'updated_count' => $updated
        ]);
    }

    // Delete a message
    public function destroy(Request $request, $messageId)
    {
        $message = Message::where('id', $messageId)
                         ->where('sender_id', $request->user()->id)
                         ->first();

        if (!$message) {
            return response()->json(['error' => 'Message not found or access denied'], 404);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully']);
    }

    // Get messages related to a property
    public function propertyMessages(Request $request, $propertyId)
    {
        $user = $request->user();
        
        $messages = Message::where('property_id', $propertyId)
            ->where(function($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->with(['sender:id,name,profile_image', 'recipient:id,name,profile_image'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ]
        ]);
    }
}
