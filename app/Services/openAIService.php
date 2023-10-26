<?php

namespace App\Services;

use GuzzleHttp\Client;
use OpenAI\Laravel\Facades\OpenAI;

class LLM
{
    static function OpenAI(){

    }
}

class openAIService
{
    public $response;
    // public function transcribe($filePath, $language)
    // {
    //     $response = OpenAI::audio()->transcribe([
    //         'model' => 'whisper-1',
    //         'file' => fopen(storage_path('app/'.$filePath), 'r'),
    //         'language' => $language,
    //         'response_format' => 'verbose_json',
    //     ]);

    //     return $response;
    // }
    public function ask(){

        // public function makeRequest()
        // {
            // Disable SSL certificate verification
           
            $client = new Client(['verify' => true,]);
    
            try {
                $response = $client->post('https://generativelanguage.googleapis.com/v1beta2/models/text-bison-001:generateText', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                        'query'=>[
                            "key"=>env('LLM_API_KEY'),
                        ],
                    'json' => [
    
                        // @PALM params
                        
                        'prompt' =>array('text'=>'what is a computer?'),  
                        "temperature"=>0.7, 
                        "candidate_count"=>1,
                        "maxOutputTokens"=>600, 
                        "topP"=>10, 
                        "topK"=>0.1
    
                    ],
                ]);
    
                $result = json_decode($response->getBody()->getContents(),true);
               
                $response=explode(".", $result['candidates'][0]['output']);
                $response="";
                foreach($response as $statement){
                    $response.=trim($statement). ".\n";
                    // echo $this->$response;
                }
                return $response;
    
            } catch (RequestException $e) {
                // Handle any request exception here
                return response()->json(['error' => $e->getMessage()], 500);
            }
    }
    // get the response from palm api
    public function response(Request $request)
    {
        //call the chat function;
        $this->ask();
        return $response;
        // return 'hello there';
    }

    public function askgpt($chatBoxModel, $chatBoxMaxTokens, $chatBoxTemperature, $transactions)
    //ask-func_name initial name
    {
        $response = OpenAI::chat()->create([
            'model' => $chatBoxModel,
            'messages' => $transactions,
            'max_tokens' => (int) $chatBoxMaxTokens,
            'temperature' => (float) $chatBoxTemperature,
        ]);

        return $response;
    }

    public function availableGPTModels()
    {
        return [
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'gpt-3.5-turbo-16k' => 'GPT-3.5 Turbo 16k',
            'gpt-4' => 'GPT-4',
            'gpt-4-32k' => 'GPT-4 32k',
        ];
    }

    public function availableGPTRoles()
    {
        $client = new Client();
        $response = $client->get('https://raw.githubusercontent.com/f/awesome-chatgpt-prompts/main/prompts.csv');
        $records = [];
        $headers = null;
        $csvString = $response->getBody();
        // Remove the first line and last line
        $csvString = substr($csvString, strpos($csvString, "\n") + 1);
        $csvString = substr($csvString, 0, strrpos($csvString, "\n"));
        $prompts = [];

        foreach (explode("\n", $csvString) as $line) {
            $values = str_getcsv($line);

            $promptName = trim($values[0], '"');
            $promptDescription = trim($values[1], '"');

            $prompts[$promptName] = $promptDescription;
        }

        return $prompts;
    }
}
