# RSS to Bluesky
RSS to Bluesky is a simple, built in a weekend, application that publishes posts from RSS feeds to a Bluesky account with the following features:

### Support multiple RSS feed sources
RSS to Bluesky supports multiple RSS feed sources, useful in the following scenarios:

- You want to post from a site that has multiple RSS feeds. This is typically found on news sites that split RSS feeds by topic.
- You run a thematic Bluesky account that wants to post content from multiple sites.

### Post with a website card embed using Open Graph data
If available, RSS to Bluesky will use Open Graph data from the RSS feed post URL to create a website card embed including image and description for the Bluesky post. If not available, it will create a simple website card embed.

### Removes duplicate RSS feed posts
RSS to Bluesky fingerprints RSS posts and removes any duplicates.

### Limit the number of RSS feed posts posted to Bluesky
RSS to Bluesky can optionally limit how many RSS feed posts are processed at a time. This is useful on first run or if the RSS feeds publish too many posts at a time and you don’t want to flood your Bluesky account.

RSS to Bluesky always posts the oldest RSS feed posts first so by using a post limit, you can process a large amount of posts by running RSS to Bluesky with a sensible pause before running again.

## Requirements
PHP 8.* with default packages. Optionally running a local Memcached server with the PHP Memcached package enabled is recommended (see limitations below).

## Limitations
### Rate limits
Every time RSS to Bluesky runs, it uses session tokens to authenticate with Bluesky. These session tokens are obtained via `com.atproto.server.createSession` which is currently rate limited to 30 requests per 5 minutes and 300 requests per day for each account. If you're running RSS to Bluesky every five minutes, this equates to 288 request a day so should not be an issue but if RSS to Bluesky is run more often, you're likely to exceed rate limits and be unable to post until this rate limit resets.

To get around this limitation, RSS to Bluesky will use local Memcached if available to temporarily store and refresh these tokens so the rate limit is not exceeded. RSS to Bluesky displays messages confirming Memcached is working, if you do not see these messages, be careful with rate limits.

### Duplicate posts
To prevent duplicates, RSS to Bluesky fingerprints RSS feed posts and ignores posts with the same fingerprint. This works in most cases but some sites will repost with modified data which defeats duplicate detection. News sites in particular do this during breaking news so there is usually no harm in reposting as information changes.

## Configuration
To configure the application, set up a `.env` file in the same root directory as `rss-to-bluesky.php` with the following values:

- `BLUESKY_HANDLE`: The Bluesky account handle you want to post RSS feeds to without the @ mark.
- `BLUESKY_APP_PASSWORD`: An app password for this Bluesky account. This app password doesn’t need access to direct messages. Don’t be that person who uses the account password instead.
- `RSS_FEEDS`: A single or comma-separated list of RSS feed URLs you want to post from.

Example `.env` file for a single RSS feed:

```
BLUESKY_HANDLE=your_bluesky_handle
BLUESKY_APP_PASSWORD=your_bluesky_app_password
RSS_FEEDS=https://example.com/feed.xml
```

Example `.env` file for multiple RSS feeds:

```
BLUESKY_HANDLE=your_bluesky_handle
BLUESKY_APP_PASSWORD=your_bluesky_app_password
RSS_FEEDS=https://example.com/feed1.xml,https://example.com/feed2.xml
```

RSS to Bluesky also supports the following optional `.env` values:

- `BLUESKY_POST_LANGUAGES`: A single or comma-separated list of BCP-47 format post languages. The default post language is 'en'.
- `BLUESKY_POST_LIMIT`: The maximum amount of RSS feed posts posted to Bluesky at one time. The default post limit is no limit.
- `RSS_MAX_AGE`: The maximum age in hours in the past an RSS feed post publication date must be below to be posted to Bluesky. The default maximum age is 24 hours.
- `BLUESKY_HOST`: The Bluesky account hosting provider. Unless you’ve setup your own Bluesky PDS, you don’t need to set this value. The default host is `https://bsky.social`.
- `DRY_RUN`: A boolean which when enabled will do everything except post to Bluesky. Dry run will still authenticate with Bluesky so please keep in mind the limitations above.

Example `.env` file with Japanese language posts:

```
BLUESKY_HANDLE=your_bluesky_handle
BLUESKY_APP_PASSWORD=your_bluesky_app_password
RSS_FEEDS=https://example.com/feed.xml
BLUESKY_POST_LANGUAGES=ja
```

Example `.env` file with Japanese and English language posts:

```
BLUESKY_HANDLE=your_bluesky_handle
BLUESKY_APP_PASSWORD=your_bluesky_app_password
RSS_FEEDS=https://example.com/feed.xml
BLUESKY_POST_LANGUAGES=ja,en
```

Example `.env` file with a five post limit:

```
BLUESKY_HANDLE=your_bluesky_handle
BLUESKY_APP_PASSWORD=your_bluesky_app_password
RSS_FEEDS=https://example.com/feed.xml
BLUESKY_POST_LIMIT=5
```

Example `.env` file with a maximum RSS feed post age of 2 hours ago:

```
BLUESKY_HANDLE=your_bluesky_handle
BLUESKY_APP_PASSWORD=your_bluesky_app_password
RSS_FEEDS=https://example.com/feed.xml
RSS_MAX_AGE=2
```

Example `.env` file with a custom host:

```
BLUESKY_HOST=https://bluesky.example.com
BLUESKY_HANDLE=your_bluesky_handle
BLUESKY_APP_PASSWORD=your_bluesky_app_password
RSS_FEEDS=https://example.com/feed.xml
```

Example `.env` file with dry run enabled:

```
BLUESKY_HANDLE=your_bluesky_handle
BLUESKY_APP_PASSWORD=your_bluesky_app_password
RSS_FEEDS=https://example.com/feed.xml
DRY_RUN=true
```

Example `.env` file with all options:

```
BLUESKY_HOST=https://bluesky.example.com
BLUESKY_HANDLE=your_bluesky_handle
BLUESKY_APP_PASSWORD=your_bluesky_app_password
RSS_FEEDS=https://example.com/feed.xml
BLUESKY_POST_LANGUAGES=ja
BLUESKY_POST_LIMIT=5
RSS_MAX_AGE=2
DRY_RUN=true
```

Make sure to keep your `.env` file secure and do not share it publicly.

## Running RSS to Bluesky
To run once in terminal:

```
php /path/to/rss-to-bluesky.php
```

To run automatically, use cron:

```
crontab -e
```

Then add something like the following to your crontab file and save. This example runs RSS to Bluesky every five minutes, ignoring output:

```
*/5 * * * * /path/to/php /path/to/rss-to-bluesky.php  > /dev/null 2>&1
```

## Contributing
If you can handle tabs and Allman formatting, fork and drop a pull request.