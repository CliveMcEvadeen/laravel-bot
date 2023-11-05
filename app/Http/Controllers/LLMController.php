<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use Illuminate\http\JsonResponse;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Http\Request;
use App\Models\Chatbox;

class LLMController extends Controller
{
    public $LLM_response;

    public function RetrieveConversation(){
        // this method helps in handling the coversation context
        // it fetches the messages from the db and pass it to the llm
        // authenticate current user
        $user = Auth::user();
        if ($user){
            // check if true user
            $userId = Auth::id();
            try {
                // Fetch messages from the chatboxes table for a specific user
                $messages = ChatBox::where('user_id', $userId)->pluck('messages')->toArray();

                return $messages;
            } catch (\Exception $e) {
                // Handle database connection error
                report($e); 
            }
        }
        else{
            return '';
        }
    }
    
    public function makeRequest($user_query)
    {
        $databaseHistory = $this->RetrieveConversation();
        // Combine user input, database history, and previous history
        $conversationHistory = array_merge(["$user_query"], $databaseHistory);

         // Limit the context window size to the last N turns
        $conversationHistory = array_slice($conversationHistory, -5, 5);

        // Combine user input, database history, and previous history into a single string
        $modelInput = implode("\n", $conversationHistory);
        // Enable SSL certificate verification---ssl{cert}.pem
       
        $client = new Client(["verify" => true,]);

        try {
            $instructions=[];

            $response = $client->post("https://generativelanguage.googleapis.com/v1beta2/models/text-bison-001:generateText", [
                "headers" => [
                    "Content-Type" => "application/json",
                ],
                    "query"=>[
                        "key"=>env("GOOGLE_API_KEY"),
                    ],
                "json" => [

                    // @PALM_API params
                    
                    "prompt" =>array("text"=> $modelInput),  
                    "temperature"=>0.8, 
                    "candidate_count"=>1, 
                    "maxOutputTokens"=>200, 
                    "topP"=>0.8, 
                    "topK"=>10

                ],
            ]);

            $result = json_decode($response->getBody()->getContents(),true);
           
            $response=explode(".", $result["candidates"][0]["output"]);
            $this->LLM_response="";
            foreach($response as $statement){
                $this->LLM_response.=trim($statement). ".\n";
            }
        } catch (RequestException $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function Response($query)
    {
        $this->makeRequest($query);
        return $this->LLM_response;
    }

    public function view_render(){
        $this->makeRequest();
        $response = $this->LLM_response;

        return view("page", ['response'=> $response]);
    }

}