<?php

namespace App\Http\Livewire;

use App\Models\ChatBox as ChatBoxModel;
use App\Services\openAIService;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use App\Http\Controllers\LLMController;


class ChatBox extends Component
{
    use LivewireAlert;

    public $chatbox;

    public $message;

    public $transactions = [];

    public $messages = [];

    public $chatBoxRole;

    public $totalTokens = 1;

    public $showSystemInstruction = false;

    public $chatBoxSystemInstruction = 'what is the longest river in Africa';

    protected $openAIService;

    public function boot(openAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function mount(ChatBoxModel $chatbox)
    {
        if ($chatbox->exists) {
            $this->messages = json_decode($chatbox->messages, true);
            // Preparing saved messages to be loaded in transactions array
            $saved_messages = array_values(json_decode($chatbox->messages, true));
            foreach ($saved_messages as $saved_message) {
                $this->transactions[] = ['role' => $saved_message['role'], 'content' => $saved_message['content']];
            }
        }

    }
    
    public function ask()
    {
        $this->transactions[] = ['role' => 'system', 'content' => $this->chatBoxSystemInstruction];

        //instatiate the LLm controller to handle the user query

        $response=new LLMController();
        
        if (! empty($this->message)) {
            $this->transactions[] = ['role' => 'user', 'content' => $this->message];
            // $totalTokens+=str_word_count($this->message['content']);
            $response->Response($this->message);
            $this->transactions[] = ['role' => 'assistant', 'content' => $response->Response($this->message)];
            $this->messages = collect($this->transactions)->reject(fn ($message) => $message['role'] === 'system');
            $this->message = '';
        }

    }

    public function sendChatToEmail()
    {
        if ($this->messages === []) {
            $this->alert('error', 'You have not started a conversation yet!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } else {
            $details = [
                'email' => auth()->user()->email,
                'messages' => $this->messages,
            ];
            dispatch(new \App\Jobs\SendEmailJob($details));
            $this->alert('success', 'Your email was sent successfully!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        }
    }

    public function updatedChatBoxRole($value)
    {
        $this->message = $value;
    }

    public function resetChatBox()
    {
        return redirect()->route('chatbox');
    }

    // public function tokens(){
    //     // token counter
    //     foreach($this->messages as $tokens){
    //         if($tokens['role']==="assistant"){
    //             $string=$tokens["content"];
    //             $tokens=str_word_count($tokens);
    //         $totalTokens+=$tokens;
    //         }
    //     }
    //     return $totalTokens;
    // }

    public function saveChat()
    {
        if ($this->messages === []) {
            $this->alert('error', 'You have not started a conversation yet!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
            ]);
        } else {
            if ($this->chatbox->exists) {
                $this->chatbox->update([
                    'messages' => $this->messages,
                    'total_tokens' => 1,
                ]);
                $this->message = '';
                $this->alert('success', 'Your chat was updated successfully!', [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
            } else {

                $chatBox = new ChatBoxModel();
                $chatBox->user_id = auth()->user()->id;
                $chatBox->messages = $this->messages;
                $chatBox->total_tokens = 1;
                $chatBox->save();
                $this->message = '';
                $this->alert('success', 'Your chat was saved successfully!', [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.chat-box.chat-box', [
            'availableGPTModels' => $this->openAIService->availableGPTModels(),
            'availableGPTRoles' => $this->openAIService->availableGPTRoles(),
        ]);
    }

    
}
