<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Create a model for storing conversation context
class ConversationContext extends Model
{
    protected $fillable = ['user_id', 'conversation_id', 'context_data'];
}

// Store context in the database
$context = new ConversationContext([
    'user_id' => auth()->id(),
    'conversation_id' => $conversationId,
    'context_data' => ['user_intent' => 'book_flight'],
]);
$context->save();

// Retrieve context from the database
$context = ConversationContext::where('user_id', auth()->id())
                             ->where('conversation_id', $conversationId)
                             ->first();
$contextData = $context->context_data;
