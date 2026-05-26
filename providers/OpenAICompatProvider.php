<?php
/**
 * OpenAI-compatible provider (Groq, Cerebras, SambaNova, NVIDIA, Mistral, OpenRouter, GitHub, HuggingFace, Zhipu)
 */

require_once __DIR__ . '/BaseProvider.php';

class OpenAICompatProvider extends BaseProvider {
    public string $platform;
    public string $name;
    private string $baseUrl;
    private array $extraHeaders;
    private ?string $validateUrl;
    private int $timeoutMs;
    
    public function __construct(string $platform, string $name, string $baseUrl, array $extraHeaders = [], ?string $validateUrl = null, int $timeoutMs = 15000) {
        $this->platform = $platform;
        $this->name = $name;
        $this->baseUrl = $baseUrl;
        $this->extraHeaders = $extraHeaders;
        $this->validateUrl = $validateUrl;
        $this->timeoutMs = $timeoutMs;
    }
    
    public function chatCompletion(string $apiKey, array $messages, string $modelId, ?array $options = null): array {
        $headers = array_merge(
            [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            $this->extraHeaders
        );
        
        $body = json_encode([
            'model' => $modelId,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? null,
            'max_tokens' => $options['max_tokens'] ?? null,
            'top_p' => $options['top_p'] ?? null,
            'tools' => $options['tools'] ?? null,
            'tool_choice' => $options['tool_choice'] ?? null,
            'parallel_tool_calls' => $options['parallel_tool_calls'] ?? null,
        ], JSON_UNESCAPED_SLASHES);
        
        $result = $this->fetchWithTimeout($this->baseUrl . '/chat/completions', [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $body,
        ], $this->timeoutMs);
        
        if ($result['status'] >= 400) {
            $errorData = json_decode($result['body'], true);
            $errorMsg = $errorData['error']['message'] ?? $result['status'];
            throw new Exception("{$this->name} API error {$result['status']}: $errorMsg");
        }
        
        $data = json_decode($result['body'], true);
        $data['_routed_via'] = ['platform' => $this->platform, 'model' => $modelId];
        
        return $data;
    }
    
    public function streamChatCompletion(string $apiKey, array $messages, string $modelId, ?array $options = null) {
        $headers = array_merge(
            [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            $this->extraHeaders
        );
        
        $body = json_encode([
            'model' => $modelId,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? null,
            'max_tokens' => $options['max_tokens'] ?? null,
            'top_p' => $options['top_p'] ?? null,
            'tools' => $options['tools'] ?? null,
            'tool_choice' => $options['tool_choice'] ?? null,
            'parallel_tool_calls' => $options['parallel_tool_calls'] ?? null,
            'stream' => true,
        ], JSON_UNESCAPED_SLASHES);
        
        // For streaming, we need to use a different approach
        // This is a simplified version - full implementation would use curl with CURLOPT_WRITEFUNCTION
        $result = $this->fetchWithTimeout($this->baseUrl . '/chat/completions', [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $body,
        ], $this->timeoutMs);
        
        if ($result['status'] >= 400) {
            $errorData = json_decode($result['body'], true);
            $errorMsg = $errorData['error']['message'] ?? $result['status'];
            throw new Exception("{$this->name} API error {$result['status']}: $errorMsg");
        }
        
        // Parse SSE stream and yield chunks
        $lines = explode("\n", $result['body']);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || !str_starts_with($line, 'data: ')) {
                continue;
            }
            $data = substr($line, 6);
            if ($data === '[DONE]') {
                break;
            }
            $chunk = json_decode($data, true);
            if ($chunk) {
                yield $chunk;
            }
        }
    }
    
    public function validateKey(string $apiKey): bool {
        $url = $this->validateUrl ?? $this->baseUrl . '/models';
        $headers = array_merge(
            ['Authorization: Bearer ' . $apiKey],
            $this->extraHeaders
        );
        
        $result = $this->fetchWithTimeout($url, [
            'method' => 'GET',
            'headers' => $headers,
        ], 10000);
        
        return $result['status'] !== 401 && $result['status'] !== 403;
    }
}
