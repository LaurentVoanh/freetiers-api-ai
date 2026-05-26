# FreeLLMAPI - PHP SQLite Version

A powerful, self-hosted LLM API gateway that aggregates multiple free-tier AI providers with intelligent routing and automatic fallback.

## Features

- **Multi-Provider Support**: Groq, Cerebras, SambaNova, NVIDIA NIM, Mistral, OpenRouter, GitHub Models, Cohere, Cloudflare, Zhipu AI, HuggingFace
- **Intelligent Routing**: Automatic selection of best available provider based on intelligence rank, speed, and rate limits
- **Auto-Fallback**: Seamless failover when providers are rate-limited or unavailable
- **Rate Limiting**: Per-provider/per-model rate limit tracking (RPM, RPD, TPM, TPD)
- **Encryption**: AES-256-GCM encryption for all stored API keys
- **Streaming Support**: Real-time SSE streaming responses
- **Unified API**: Single endpoint for all providers
- **Futuristic UI**: Cyberpunk-inspired design inspired by 2Advanced Studio

## Project Structure

```
PHP V0/
├── index.php              # Main entry point & router
├── config.php             # Configuration settings
├── database.php           # SQLite database management
├── crypto.php             # Encryption utilities
├── router.php             # Request routing logic
├── ratelimit.php          # Rate limiting service
├── api/
│   ├── chat.php           # Chat completion endpoint
│   ├── models.php         # List available models
│   └── stats.php          # Usage statistics
├── pages/
│   ├── home.php           # Chat interface
│   ├── setup.php          # API key setup guide
│   ├── admin.php          # Admin dashboard
│   └── api.php            # API documentation
├── providers/
│   ├── BaseProvider.php   # Abstract provider base class
│   ├── OpenAICompatProvider.php  # OpenAI-compatible providers
│   └── ProviderRegistry.php      # Provider registration
├── assets/
│   ├── css/style.css      # Futuristic cyberpunk styles
│   └── js/app.js          # Frontend JavaScript
└── data/
    └── freeapi.db         # SQLite database (auto-created)
```

## Installation

### Requirements
- PHP 7.4+ with SQLite3 extension
- OpenSSL extension for encryption
- cURL extension for API requests

### Quick Start

1. **Clone or copy the PHP V0 folder** to your web server

2. **Set environment variable** (optional, key can be auto-generated):
   ```bash
   export ENCRYPTION_KEY=$(openssl rand -hex 32)
   ```

3. **Ensure write permissions** for the data directory:
   ```bash
   chmod 755 PHP\ V0/data
   ```

4. **Access the application** in your browser:
   ```
   http://localhost:3001/
   ```

5. **Get free API keys** from providers (see Setup Guide page)

6. **Add API keys** in the Admin panel

## Configuration

Edit `config.php` to customize:
- Database path
- Server port/host
- Rate limit windows
- Retry settings
- Penalty system parameters

## API Usage

### Unified Chat Endpoint

```bash
curl -X POST http://localhost:3001/api/chat.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_UNIFIED_KEY" \
  -d '{
    "messages": [{"role": "user", "content": "Hello!"}],
    "stream": true
  }'
```

### Get Available Models

```bash
curl http://localhost:3001/api/models.php
```

### Get Statistics

```bash
curl http://localhost:3001/api/stats.php
```

## Supported Providers

| Provider | RPM | TPM | Notes |
|----------|-----|-----|-------|
| Groq | 30 | 6,000 | Ultra-fast Llama inference |
| Cerebras | 30 | 60,000 | Frontier models |
| SambaNova | 20 | 200K/day | High-performance |
| OpenRouter | 20 | Varies | Multiple free models |
| GitHub Models | 10 | 50/day | GPT-5 access |
| Mistral | 2 | 500,000 | European provider |
| Cohere | 20 | 33/day | Enterprise models |
| Cloudflare | - | ~18-45M/month | Edge computing |
| HuggingFace | - | ~1-3M | Open-source models |
| Zhipu AI | - | 1M/day | GLM series |
| NVIDIA NIM | Credits | Credits | Accelerated inference |

## Security

- All API keys encrypted with AES-256-GCM before storage
- Unified API key authentication for endpoints
- CORS headers configured for web access
- No keys exposed in frontend code

## Advanced Features

### Dynamic Priority System
- Penalties applied on rate limit hits
- Time-based penalty decay
- Sticky sessions for consistent routing

### Rate Limiting
- Persistent tracking in SQLite
- Memory-based fallback
- Cooldown periods for rate-limited keys

### Fallback Chain
- Models ranked by intelligence and speed
- Automatic retry on failure
- Skip list to avoid repeated failures

## Troubleshooting

### Database not created
Ensure the `data/` directory is writable by the web server.

### Encryption key error
Set `ENCRYPTION_KEY` environment variable (64 hex characters).

### No providers available
Add at least one valid API key in the Admin panel.

### Streaming not working
Check that output buffering is disabled and `flush()` is supported.

## License

MIT License - Feel free to use and modify.

## Credits

Inspired by the need for reliable, free LLM access through provider aggregation.
