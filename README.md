# AI PR Inspector

<p style="text-align: center;">
  <strong>🔍 AI-Powered PR Analysis System</strong>
</p>

<p style="text-align: center;">
  <img src="https://img.shields.io/badge/Laravel-13-red" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.5-blue" alt="PHP">
  <img src="https://img.shields.io/badge/AI-Claude/OpenAI-purple" alt="AI">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

<p style="text-align: center;">
  <a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License"></a>
</p>

## 📋 Overview

AI PR Inspector is a sophisticated Laravel-based application that automatically analyzes pull requests using multiple AI providers (Claude, OpenAI). It integrates with Gitea to provide intelligent code analysis, security vulnerability detection, and comprehensive feedback on code changes. This project demonstrates advanced software architecture, AI integration, and production-ready development practices.

### ✨ Key Features

- 🤖 **Multi-Provider AI Support**: Claude and OpenAI with configurable fallbacks
- 🔄 **Webhook Integration**: Real-time pull request processing via Gitea webhooks
- 📊 **Progress Tracking**: Complete audit trail with database monitoring
- ⚡ **Rate Limiting**: Intelligent API rate limiting per provider
- 🔒 **Security-First**: HTTPS enforcement, webhook signature verification
- 📈 **Production Ready**: Comprehensive logging, error handling, and monitoring
- 🧪 **Well Tested**: Extensive PHPUnit test coverage

## 🏗️ Architecture

### System Components

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Gitea     │───▶│   Webhook   │───▶│    Queue    │
│ Repository  │    │  Controller │    │   System    │
└─────────────┘    └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │   Database  │    │  AI Review  │
                   │   Tracking  │    │    Job      │
                   └─────────────┘    └─────────────┘
                                            │
                                            ▼
                                    ┌─────────────┐
                                    │ AI Providers │
                                    │  Claude/OAI  │
                                    └─────────────┘
```

### Core Services

- **WebhookController**: Handles Gitea webhook events and dispatches jobs
- **ProcessPullRequestJob**: Main job for processing pull requests
- **ChunkService**: Intelligently splits large diffs into manageable chunks
- **AIService**: Multi-provider AI service with rate limiting
- **CommentService**: Posts reviews back to Gitea
- **RateLimitService**: Per-provider API rate limiting

### AI Provider Architecture

```
AIProviderInterface
├── ClaudeProvider
│   ├── Rate limiting (50 RPM / 1000 RPH)
│   └── Claude API integration
└── OpenAIProvider
    ├── Rate limiting (60 RPM / 3500 RPH)
    └── OpenAI API integration
```

## 🗄️ Database Schema

### Tables

#### `pull_request_reviews`
Tracks the status and progress of pull request reviews.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `repo_name` | string | Repository name |
| `pr_number` | integer | Pull request number |
| `commit_sha` | string | Commit SHA |
| `action` | string | PR action (opened, synchronize) |
| `payload` | json | Full webhook payload |
| `status` | string | pending/processing/completed/failed |
| `files_count` | integer | Number of files to review |
| `chunks_count` | integer | Number of chunks created |
| `processed_chunks` | integer | Number of chunks processed |
| `error_message` | text | Error message if failed |
| `started_at` | timestamp | Processing start time |
| `completed_at` | timestamp | Processing completion time |

#### `users`
Standard Laravel users table (for future authentication features).

#### `jobs`
Laravel queue jobs table for background processing.

#### `cache`
Laravel cache table for rate limiting and caching.

### Model Relationships

```php
PullRequestReview
├── Status: pending → processing → completed/failed
├── Methods: markAsProcessing(), markAsCompleted(), markAsFailed()
└── Scopes: forRepo(), forPr(), withStatus()
```

## 🚀 Setup & Installation

### Prerequisites

- PHP 8.5+
- MySQL 8.0+ or PostgreSQL 12+
- Redis (for caching and queues)
- Composer
- Node.js & NPM (for frontend assets)

### Installation Steps

1. **Clone the repository**
```bash
git clone <repository-url>
cd ai-reviewer
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure environment variables** (see Configuration section below)

5. **Database setup**
```bash
php artisan migrate
php artisan db:seed
```

6. **Build frontend assets**
```bash
npm run build
```

7. **Start queue workers**
```bash
php artisan queue:work --queue=ai-review --tries=3 --timeout=300
```

8. **Setup web server** (Apache/Nginx with SSL)

## ⚙️ Configuration

### Environment Variables

#### Core Application
```env
APP_NAME="AI Code Reviewer"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# HTTPS Settings
FORCE_HTTPS=true
```

#### Database
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_reviewer
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Queue & Cache
```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### AI Providers
```env
# Claude AI Configuration
CLAUDE_API_KEY=your_claude_api_key
CLAUDE_MODEL=claude-3-5-sonnet-20241022
CLAUDE_RATE_LIMIT_RPM=50
CLAUDE_RATE_LIMIT_RPH=1000
CLAUDE_RETRY_AFTER=60

# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-4
OPENAI_RATE_LIMIT_RPM=60
OPENAI_RATE_LIMIT_RPH=3500
OPENAI_RETRY_AFTER=60

# AI Service Configuration
AI_DEFAULT_PROVIDER=claude
AI_FALLBACK_ENABLED=true
AI_FALLBACK_PROVIDER=openai
```

#### Gitea Integration
```env
GITEA_BASE_URL=https://your-gitea-instance.com
GITEA_TOKEN=your_gitea_token
GITEA_WEBHOOK_SECRET=your_webhook_secret
```

### AI Provider Configuration

Rate limits can be configured per provider in `config/ai.php`:

```php
'providers' => [
    'claude' => [
        'rate_limit' => [
            'requests_per_minute' => 50,
            'requests_per_hour' => 1000,
            'retry_after_seconds' => 60,
        ],
    ],
    'openai' => [
        'rate_limit' => [
            'requests_per_minute' => 60,
            'requests_per_hour' => 3500,
            'retry_after_seconds' => 60,
        ],
    ],
],
```

## 🔌 API Endpoints

### Webhook Endpoint
```
POST /api/webhook/gitea
```

**Headers Required:**
- `X-Gitea-Event`: `pull_request`
- `X-Gitea-Signature`: HMAC-SHA256 signature

**Rate Limiting:** 60 requests per minute

### Health Check
```
GET /api/health
```

Returns service health status including AI provider configuration and database connectivity.

### Example Webhook Payload
```json
{
  "action": "opened",
  "number": 123,
  "repository": {
    "full_name": "user/repo"
  },
  "head": {
    "sha": "commit-sha"
  }
}
```

## 🔄 How It Works

1. **Pull Request Created**: Developer creates/updates a PR in Gitea
2. **Webhook Triggered**: Gitea sends webhook to `/api/webhook/gitea`
3. **Signature Verification**: Webhook signature is validated
4. **Job Dispatched**: `ProcessPullRequestJob` is queued
5. **File Retrieval**: Job fetches changed files from Gitea API
6. **Chunking**: Large diffs are split into manageable chunks
7. **AI Analysis**: Each chunk is sent to AI provider for review
8. **Comment Posting**: Reviews are posted as comments on the PR
9. **Status Tracking**: Progress is tracked in `pull_request_reviews` table

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --filter=AIServiceTest
php artisan test --filter=ForceHttpsMiddlewareTest
php artisan test --filter=ChunkServiceTest

# Run with coverage
php artisan test --coverage
```

### Test Coverage
- **AI Service Tests**: Provider creation, configuration, review generation
- **HTTPS Middleware Tests**: HTTP/HTTPS redirects, environment detection
- **Chunk Service Tests**: File processing, filtering, chunking logic

## 🚀 Deployment

### Production Checklist

#### Security
- [ ] `APP_DEBUG=false`
- [ ] `FORCE_HTTPS=true`
- [ ] SSL certificate installed
- [ ] Webhook secrets configured
- [ ] API keys secured (use secrets management)

#### Infrastructure
- [ ] Database configured and migrated
- [ ] Redis running for caching/queues
- [ ] Queue workers running with supervisor
- [ ] Log rotation configured
- [ ] Monitoring and alerting setup

#### Performance
- [ ] Queue workers optimized
- [ ] Database indexes optimized
- [ ] Rate limits configured appropriately
- [ ] Caching enabled

#### Monitoring
- [ ] Health endpoint accessible
- [ ] Error tracking (Sentry, etc.)
- [ ] Performance monitoring
- [ ] Queue monitoring

### Docker Deployment

```dockerfile
FROM php:8.5-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . /var/www/html

# Install dependencies
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
```

### Supervisor Configuration

```ini
[program:ai-reviewer-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --queue=ai-review --sleep=3 --tries=3 --max-time=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/ai-reviewer-queue.log
stopwaitsecs=3600
```

## 📊 Monitoring & Logging

### Log Channels
- **Single**: Application logs (`storage/logs/laravel.log`)
- **Queue**: Job processing logs
- **AI Provider**: API request/response logs

### Key Metrics to Monitor
- Queue job processing time
- API rate limit utilization
- Error rates by provider
- Database query performance
- Memory usage during processing

### Health Check Response
```json
{
  "status": "healthy",
  "timestamp": "2026-05-02T12:00:00Z",
  "version": "1.0.0",
  "environment": "production",
  "services": {
    "claude": {
      "status": "healthy",
      "model": "claude-3-5-sonnet-20241022",
      "api_key_configured": true
    },
    "gitea": {
      "status": "healthy",
      "base_url_configured": true,
      "token_configured": true,
      "webhook_secret_configured": true
    },
    "database": {
      "status": "healthy"
    },
    "queue": {
      "status": "healthy"
    }
  }
}
```

## 🔧 Troubleshooting

### Common Issues

#### Jobs Not Processing
```bash
# Check queue status
php artisan queue:failed

# Restart queue workers
php artisan queue:restart

# Clear stuck jobs
php artisan queue:clear
```

#### Rate Limiting Issues
- Check provider rate limits in logs
- Verify `*_RATE_LIMIT_*` environment variables
- Monitor remaining requests via health endpoint

#### Database Issues
- Verify database connection
- Check migration status: `php artisan migrate:status`
- Review database logs

#### AI Provider Issues
- Verify API keys are valid
- Check model availability
- Review provider-specific error logs

### Debug Mode
```bash
# Enable debug logging
php artisan tinker
>>> config(['app.debug' => true]);

# Test AI service
php artisan tinker
>>> $ai = new App\Services\AIService();
>>> $review = $ai->review(['file' => 'test.php', 'patch' => '<?php echo "test"; ?>']);
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

### Development Setup
```bash
# Install development dependencies
composer install
npm install

# Run tests
php artisan test

# Code style
./vendor/bin/pint

# Start development server
php artisan serve
```

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🔗 Related Projects

- [Laravel Framework](https://laravel.com/)
- [Gitea](https://gitea.io/)
- [Claude API](https://docs.anthropic.com/claude/reference)
- [OpenAI API](https://platform.openai.com/docs/api-reference)

## 📞 Support

For questions and support:

1. Check the [troubleshooting section](#-troubleshooting)
2. Review existing [issues](https://github.com/your-repo/issues)
3. Create a new issue with detailed information

---

**Built with ❤️ using Laravel and powered by AI**
