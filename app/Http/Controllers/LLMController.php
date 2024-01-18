<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use App\Models\ChatBox;
use Illuminate\Support\Facades\Log;
use GeminiAPI\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Session;

class LLMController extends Controller
{
    function addToChatHistory($message, $role) {

    /**
     * Adds a message to the chat history.
     *
     * @param mixed $message The message to be added to the chat history.
     * @param mixed $role The role of the user who sent the message.
     * @throws Exception If there was an error retrieving the chat history from the session.
     * @return void
     */
    
        try {
            $chatHistory = Session::get('chat_history', []);
    
            if (!is_array($chatHistory)) {
                throw new Exception("Error retrieving chat history from session.");
            }
    
            $chatHistory[] = ['message' => $message, 'role' => $role];
    
            Session::put('chat_history', $chatHistory);
        } catch (Exception $e) {
            throw $e;
        }
    }


    public function response($query) {

            /**
     * Response to a chat query.
     *
     * @param mixed $query The query message to send to the chat.
     * @throws Exception Error starting chat with Gemini or sending message to Gemini.
     * @return mixed The response from the chat.
     */

        try {
            $chatHistory = Session::get('chat_history', []);
            $chat = Gemini::startChat($chatHistory);
    
            if (!$chat) {
                throw new Exception("Error starting chat with Gemini.");
            }
            if($query){
                $response = $chat->sendMessage($query);
            }else{
                throw new Exception("provide message to be  sent");
            }
            
    
            if (!$response) {
                throw new Exception("Error sending message to Gemini.");
            }
    
            $this->addToChatHistory($query, "user");
            $this->addToChatHistory($response, "model");
    
            return $response;
        } catch (Exception $e) {
            throw $e;
        }
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

