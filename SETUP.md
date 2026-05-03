# AI Reviewer Setup Guide

This guide will help you set up the AI Reviewer for Gitea pull request automation using Claude AI.

## Prerequisites

- PHP 8.5+
- Composer
- MySQL or PostgreSQL database
- Redis (optional, for queues)
- Gitea instance with admin access
- Claude AI API key

## Installation

### 1. Clone and Install Dependencies

```bash
git clone <repository-url>
cd ai-reviewer
composer install
npm install
```

### 2. Environment Configuration

Copy the environment template:

```bash
cp .env.example .env
```

Edit `.env` and configure the following variables:

```env
# Claude AI Configuration
CLAUDE_API_KEY=your_claude_api_key_here
CLAUDE_MODEL=claude-3-5-sonnet-20241022

# Gitea Configuration
GITEA_BASE_URL=https://your-gitea-instance.com
GITEA_TOKEN=your_gitea_access_token
GITEA_WEBHOOK_SECRET=your_webhook_secret_here

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_reviewer
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Queue Configuration
QUEUE_CONNECTION=database
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Database Setup

Run the migrations:

```bash
php artisan migrate
```

### 5. Build Frontend Assets

```bash
npm run build
```

## Gitea Configuration

### 1. Create Gitea Access Token

1. Log in to your Gitea instance
2. Go to Settings → Applications → Access Tokens
3. Generate a new token with the following permissions:
   - `repo` (Full control)
   - `read:org` (if reviewing private repos)

### 2. Configure Webhook

1. Navigate to your repository in Gitea
2. Go to Settings → Webhooks
3. Add a new webhook with the following settings:
   - **Target URL**: `https://your-ai-reviewer-domain.com/api/webhook/gitea`
   - **Content Type**: `application/json`
   - **Secret**: Use the same value as `GITEA_WEBHOOK_SECRET`
   - **Trigger Events**: Check "Pull Request" events
   - **Active**: Enable the webhook

### 3. Test Webhook

Click "Test Delivery" in the webhook settings to verify the connection.

## Running the Application

### Development

```bash
php artisan serve
```

### Production with Queue Worker

```bash
# Start the application server
php artisan serve --host=0.0.0.0 --port=8000

# Start the queue worker (in another terminal)
php artisan queue:work --queue=ai-review --tries=3 --timeout=300

# Monitor logs (optional)
php artisan pail
```

### Using Composer Scripts

The project includes convenient composer scripts:

```bash
# Development (starts server, queue, logs, and vite)
composer run dev

# Full setup (install, migrate, build)
composer run setup

# Run tests
composer run test
```

## Configuration Options

### Claude AI Settings

- `CLAUDE_API_KEY`: Your Claude API key from Anthropic
- `CLAUDE_MODEL`: Claude model to use (default: `claude-3-5-sonnet-20241022`)

### Gitea Settings

- `GITEA_BASE_URL`: Your Gitea instance URL
- `GITEA_TOKEN`: Access token with repository permissions
- `GITEA_WEBHOOK_SECRET`: Secret for webhook signature verification

### Chunking Configuration

The `ChunkService` automatically handles large files by:

- Skipping binary files and generated files
- Splitting large patches into manageable chunks
- Filtering out lock files and vendor directories

Default limits:
- Maximum patch size: 15,000 characters
- Maximum lines per chunk: 200 lines
- Maximum file size: 50,000 characters

## Monitoring

### Health Check

Monitor the application status:

```bash
curl http://localhost:8000/api/health
```

### Logs

Monitor application logs:

```bash
php artisan pail
```

### Queue Status

Check queue worker status:

```bash
php artisan queue:monitor
```

## Security Considerations

1. **Webhook Security**: Always configure a webhook secret
2. **API Keys**: Store API keys in environment variables, never commit them
3. **Rate Limiting**: Webhook endpoint is rate-limited to 60 requests per minute
4. **Network Security**: Use HTTPS in production
5. **Database Security**: Use strong database credentials

## Troubleshooting

### Common Issues

1. **Webhook not triggering**
   - Check webhook URL is accessible
   - Verify webhook secret matches
   - Check Gitea webhook delivery logs

2. **Claude API errors**
   - Verify API key is valid
   - Check rate limits on Claude API
   - Ensure model is available

3. **Queue jobs failing**
   - Check queue worker is running
   - Verify database connection
   - Review job failure logs

4. **Database connection issues**
   - Verify database credentials
   - Check database server is running
   - Ensure database exists

### Debug Mode

Enable debug mode in `.env`:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## Performance Optimization

1. **Queue Configuration**: Use Redis for better queue performance
2. **Database Indexing**: Review indexes on `pull_request_reviews` table
3. **Caching**: Consider caching Gitea API responses
4. **Rate Limiting**: Adjust webhook rate limits as needed

## Support

For issues and questions:

1. Check application logs: `php artisan pail`
2. Review Gitea webhook delivery logs
3. Verify Claude API status and quotas
4. Check database and queue status

## License

This project is licensed under the MIT License.
