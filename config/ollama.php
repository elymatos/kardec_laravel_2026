<?php

return [
    'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
    'default_model' => env('OLLAMA_DEFAULT_MODEL', 'llama3.1:8b'),
    'timeout' => (int) env('OLLAMA_TIMEOUT', 300),
];
