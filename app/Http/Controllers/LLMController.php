<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use App\Models\ChatBox;
use Illuminate\Support\Facades\Log;
use GeminiAPI\Laravel\Facades\Gemini;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LLMController extends Controller
{
    // context handler
    public $LLM_response;

    public function retrieveConversation(ChatBox $chatBox)
    {
        try {
            $user = Auth::user();

            if ($user) {
                $userId = Auth::id();
                $messages = $chatBox->where('user_id', $userId)->pluck('messages')->toArray();
                return $messages;
            } else {
                return [];
            }
        } catch (\Exception $e) {
            Log::error("Error in retrieveConversation: " . $e->getMessage());
            return [];
        }
    }

    public function response($query)
    {
        // request to gemini
        // return Gemini::generateText($query);
        $chat = Gemini::startChat();
        return $chat->sendMessage($query);
    }



    // public function response($query)
    // {
    //     $chatContext = session('chat_context', []);
    
    //     $chat = Gemini::startChat();
    //     $geminiResponse = $chat->sendMessage($query, $chatContext);
    //     $chatContext['last_response'] = $geminiResponse;
    
    //     // Update the chat context in the session
    //     session(['chat_context' => $chatContext]);
    
    //     return $geminiResponse;
    // }
    

    public function textGenWithImage(){

        return Gemini::generateTextUsingImageFile(
            'image/jpeg',
            'elephpant.jpg',
            'Explain what is in the image',
        );

    }

    public function chat(){
        $history = [
            [
                'message' => 'user query',
                'role' => 'user',
            ],
            [
                'message' => 'ai message',
                'role' => 'model',
            ],
        ];
        $chat = Gemini::startChat($history);
        
        return $chat->sendMessage($query);
    }

    public function viewRender(Request $request, Client $client)
    // render test scheme
    {
        try {
            $response = $this->response($request, $client);
            return view("page", ['response' => $response]);
        } catch (\Exception $e) {
            Log::error("Error in viewRender: " . $e->getMessage());
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}
// ...................................................................................
class ContextManager
{
    // based on database
    protected $conversationContextModel; // Inject the model for storing context

    public function __construct(ConversationContext $conversationContextModel)
    {
        $this->conversationContextModel = $conversationContextModel;
    }

    public function getContext()
    {
        // Retrieve context from database based on user and conversation IDs
        $context = $this->conversationContextModel::where('user_id', auth()->id())
                                              ->where('conversation_id', $conversationId)
                                              ->first();
        return $context ? $context->context_data : []; // Return context data or empty array if not found
    }

    public function updateContext($newData)
    {
        // Retrieve or create context record in the database
        $context = $this->conversationContextModel::firstOrCreate([
            'user_id' => auth()->id(),
            'conversation_id' => $conversationId,
        ]);

        // Update context data
        $context->context_data = array_merge($context->context_data, $newData);
        $context->save();
    }
}


class ContextManager
{
    // based on sensions

    public function getContext()
    {
        return session('context', []); // Retrieve context from session, default to empty array
    }

    public function updateContext($newData)
    {
        $context = $this->getContext();
        $context = array_merge($context, $newData); // Combine new data with existing context
        session(['context' => $context]); // Store updated context in session
    }
}
