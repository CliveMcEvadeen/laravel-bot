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

    public function makeRequest($user_query)
    {
        // Disable SSL certificate verification
       
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
                    
                    "prompt" =>array("text"=>$user_query),  
                    "temperature"=>0.7, 
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