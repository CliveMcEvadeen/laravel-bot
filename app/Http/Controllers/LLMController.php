<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\http\JsonResponse;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Http\Request;

class LLMController extends Controller
{
    public $LLM_response;
    public function makeRequest()
    {
        // Disable SSL certificate verification
       
        $client = new Client(["verify" => true,]);

        try {
            $instructions=[

            ];
            $response = $client->post("https://generativelanguage.googleapis.com/v1beta2/models/text-bison-001:generateText", [
                "headers" => [
                    "Content-Type" => "application/json",
                ],
                    "query"=>[
                        "key"=>env("GOOGLE_API_KEY"),
                    ],
                "json" => [

                    // @PALM params
                    
                    "prompt" =>array("text"=>"what is a computer, but expain it thoroughly"),  
                    "temperature"=>0.7, 
                    "candidate_count"=>1, 
                    "maxOutputTokens"=>600, 
                    "topP"=>0.8, 
                    "topK"=>10

                ],
            ]);

            $result = json_decode($response->getBody()->getContents(),true);
           
            $response=explode(".", $result["candidates"][0]["output"]);
            $this->LLM_response="";
            foreach($response as $statement){
                $this->LLM_response.=trim($statement). ".\n";
                // echo $this->$LLM_response;
            }
            // return $this->LLM_response;
        } catch (RequestException $e) {
            // Handle any request exception here
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function Response(Request $request)
    {
        //call the chat function;
        $this->makeRequest();
        return $this->LLM_response;
        // return "hello there";
    }
    public function view_renderer(){
        //making the response available to the view
        $this->makeRequest();

        return view("", ["LLM_Messages" => "accessed from controller"]);
    }

    // public function sendToAPI(){ 
        
    //     // $apiURL=env("FLASK_URL");

    //     // $apiURL = "http://localhost:8000/sendToAPI";

    //     $this->makeRequest($prompt);
        
    //     try{
    //         $data=["text"=>"hello there"];
    //         $response = Http::post($apiURL, $data);
    //         $responseBody = $response->json();
    //         return response()->json($responseBody);

    //     }catch (\Exception $e) {
    //         return response()->json(["error" => $e->getMessage()], 500);
    //     }

    // }
}