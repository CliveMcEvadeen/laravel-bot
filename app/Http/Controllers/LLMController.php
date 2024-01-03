<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use App\Models\ChatBox;
use Illuminate\Support\Facades\Log;

class LLMController extends Controller
{
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

    public function makeRequest($user_query, Client $client)
    {
        try {
            $databaseHistory = $this->retrieveConversation(new ChatBox());
            $conversationHistory = array_merge([$user_query], $databaseHistory); // Fixing the variable interpolation
            $conversationHistory = array_slice($conversationHistory, -5, 5);
            $modelInput = implode("\n", $conversationHistory);

            $response = $client->post("https://generativelanguage.googleapis.com/v1beta2/models/text-bison-001:generateText", [
                "headers" => [
                    "Content-Type" => "application/json",
                ],
                "query" => [
                    "key" => env("GOOGLE_API_KEY"),
                ],
                "json" => [
                    "prompt" => ["text" => $modelInput],
                    "temperature" => 0.8,
                    "candidate_count" => 1,
                    "maxOutputTokens" => 200,
                    "topP" => 0.8,
                    "topK" => 10,
                    "stop_sequences" => ['.'],
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            $response = explode(".", $result["candidates"][0]["output"]);
            $this->LLM_response = "";
            foreach ($response as $statement) {
                $this->LLM_response .= trim($statement) . ".\n";
            }
        } catch (GuzzleException $e) {
            Log::error("Guzzle RequestException: " . $e->getMessage());
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function response(Request $request, Client $client)
    {
        $query = $request->input('query');
        $this->makeRequest($query, $client);
        return $this->LLM_response;
    }

    public function viewRender(Request $request, Client $client)
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
