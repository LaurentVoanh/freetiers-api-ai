<?php
/**
 * Provider registry - manages all LLM providers
 */

require_once __DIR__ . '/BaseProvider.php';
require_once __DIR__ . '/OpenAICompatProvider.php';

class ProviderRegistry {
    private static array $providers = [];
    
    public static function init(): void {
        // Google - unique Gemini API format (will be implemented separately)
        // self::register(new GoogleProvider());
        
        // Groq - OpenAI-compatible
        self::register(new OpenAICompatProvider('groq', 'Groq', 'https://api.groq.com/openai/v1'));
        
        // Cerebras - OpenAI-compatible
        self::register(new OpenAICompatProvider('cerebras', 'Cerebras', 'https://api.cerebras.ai/v1'));
        
        // SambaNova - OpenAI-compatible
        self::register(new OpenAICompatProvider('sambanova', 'SambaNova', 'https://api.sambanova.ai/v1'));
        
        // NVIDIA NIM - OpenAI-compatible
        self::register(new OpenAICompatProvider('nvidia', 'NVIDIA NIM', 'https://integrate.api.nvidia.com/v1'));
        
        // Mistral - OpenAI-compatible
        self::register(new OpenAICompatProvider('mistral', 'Mistral', 'https://api.mistral.ai/v1'));
        
        // OpenRouter - OpenAI-compatible with extra headers
        self::register(new OpenAICompatProvider(
            'openrouter', 
            'OpenRouter', 
            'https://openrouter.ai/api/v1',
            ['HTTP-Referer: http://localhost:3001', 'X-Title: FreeLLMAPI']
        ));
        
        // GitHub Models - OpenAI-compatible
        self::register(new OpenAICompatProvider('github', 'GitHub Models', 'https://models.github.ai/inference'));
        
        // Cohere - OpenAI-compatible
        self::register(new OpenAICompatProvider('cohere', 'Cohere', 'https://api.cohere.com/compatibility/v1'));
        
        // Cloudflare Workers AI - OpenAI-compatible (key = "account_id:token")
        self::register(new OpenAICompatProvider('cloudflare', 'Cloudflare', 'https://api.cloudflare.com/client/v4/accounts'));
        
        // Zhipu (Z.ai) - OpenAI-compatible
        self::register(new OpenAICompatProvider('zhipu', 'Zhipu AI', 'https://open.bigmodel.cn/api/paas/v4'));
        
        // HuggingFace Router - OpenAI-compatible
        self::register(new OpenAICompatProvider('huggingface', 'HuggingFace Router', 'https://router.huggingface.co/v1'));
    }
    
    public static function register(BaseProvider $provider): void {
        self::$providers[$provider->platform] = $provider;
    }
    
    public static function getProvider(string $platform): ?BaseProvider {
        return self::$providers[$platform] ?? null;
    }
    
    public static function hasProvider(string $platform): bool {
        return isset(self::$providers[$platform]);
    }
    
    public static function getAllProviders(): array {
        return self::$providers;
    }
}

// Initialize providers
ProviderRegistry::init();
