{{-- <div class="flex flex-col space-y-4 p-4">
    @foreach ($messages as $message)
        <div class="flex rounded-lg p-4 @if (isset($message['role']) && $message['role'] === 'assistant') bg-green-200 flex-reverse @else bg-blue-200 @endif ">
            <div class="ml-4">
                <div class="text-lg">
                    @if (isset($message['role']) && $message['role'] === 'assistant')
                        <a href="#" class="font-medium text-gray-900">Your Assistant</a>
                    @else
                        <a href="#" class="font-medium text-gray-900">You</a>
                    @endif
                </div>
                <div class="mt-1">
                    @if (isset($message['content']))
                        <p class="text-gray-600">{!! \Illuminate\Mail\Markdown::parse($message['content']) !!}</p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div> --}}
