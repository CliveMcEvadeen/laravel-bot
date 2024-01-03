<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConversationSenssionController extends Controller
{
    //read data from the database
    // pass data to the llm
    // handling user sennssions
}


// backupper controller

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

        /*
        *********************************************************************************************
        *  ->this method handles coversation context                                               *
        *  ->it fetches the messages from the db and pass it to the llm                             *
        *  ->authenticate current user                                                              *
        *  ->check if true user                                                                     *
        *  ->Fetch messages from the chatboxes table for a specific user                           *
        *  ->Handle database connection error                                                      *
        *********************************************************************************************
        */
        
        $user = Auth::user();
        if ($user){
            
            $userId = Auth::id();
            try {
                $messages = ChatBox::where('user_id', $userId)->pluck('messages')->toArray();

                return $messages;
            } catch (\Exception $e) {
                report($e); 
            }
        }
        else{
            return '';
        }
    }
    
    public function makeRequest($user_query)
    {
        /*
        *********************************************************************************************
        *    ->Combine user input, database history, and previous history                           *
        *   -> Limiting the context window size to the last N turns                                 *
        *   -> Combine user input, database history, and previous history into a single string      *
        *   -> Enable SSL certificate verification---ssl{cert}.pem                                  *
        *********************************************************************************************
        */

        $databaseHistory = $this->RetrieveConversation();
        $conversationHistory = array_merge(["$user_query"], $databaseHistory);
        $conversationHistory = array_slice($conversationHistory, -5, 5);
        $modelInput = implode("\n", $conversationHistory);
       
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
                    "topK"=>10,
                    "stop_sequences" =>['.'],

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