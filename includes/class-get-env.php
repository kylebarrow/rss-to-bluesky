<?php

/**
 * Environment Configuration Class
 *
 * Handles reading and parsing environment variables from .env file.
 *
 * @package RSS_To_Bluesky
 * @version 1.2.0
 */

/**
 * Get_Env class for environment variable management
 *
 * Loads and provides access to configuration values from the .env file.
 */
class Get_Env
{
	private $env_root;
	private $env      = [];

	/**
	 * Constructor - load environment variables
	 *
	 * Reads and parses the .env file from the project root.
	 */
	public function __construct()
	{
		$this->env_root = dirname(__DIR__);

		if (!file_exists($this->env_root . '/.env'))
		{
			echo 'No .env file found' . PHP_EOL;
		}
		else
		{
			$this->env = parse_ini_file($this->env_root . '/.env');
		}
	}

	/**
	 * Get the Bluesky host URL
	 *
	 * Returns the configured Bluesky API host or default value.
	 *
	 * @return string Bluesky API host URL
	 */
	public function get_host()
	{
		return $this->env['BLUESKY_HOST'] ?? 'https://bsky.social';
	}

	/**
	 * Get the Bluesky handle
	 *
	 * Returns the configured Bluesky user handle.
	 *
	 * @return string|null Bluesky handle or null if not configured
	 */
	public function get_handle()
	{
		if (empty($this->env['BLUESKY_HANDLE']))
		{
			echo 'No handle found in .env' . PHP_EOL;
			return null;
		}

		return $this->env['BLUESKY_HANDLE'];
	}

	/**
	 * Get the Bluesky app password
	 *
	 * Returns the configured Bluesky application password.
	 *
	 * @return string|null App password or null if not configured
	 */
	public function get_app_password()
	{
		if (empty($this->env['BLUESKY_APP_PASSWORD']))
		{
			echo 'No app password in .env' . PHP_EOL;
			return null;
		}

		return $this->env['BLUESKY_APP_PASSWORD'];
	}

	/**
	 * Get the RSS feed URLs
	 *
	 * Returns an array of RSS feed URLs to monitor.
	 *
	 * @return array Array of RSS feed URLs
	 */
	public function get_feeds()
	{
		if (empty($this->env['RSS_FEEDS']))
		{
			echo 'No feeds found in .env' . PHP_EOL;
			return [];
		}

		return array_map('trim', explode(',', $this->env['RSS_FEEDS']));
	}

	/**
	 * Get the post languages
	 *
	 * Returns an array of language codes for Bluesky posts.
	 *
	 * @return array Array of language codes (default: ['en'])
	 */
	public function get_post_languages()
	{
		if (empty($this->env['BLUESKY_POST_LANGUAGES']))
		{
			return ['en'];
		}

		return array_map('trim', explode(',', $this->env['BLUESKY_POST_LANGUAGES']));
	}

	/**
	 * Get the maximum age for RSS posts
	 *
	 * Returns the maximum age in hours for posts to be considered.
	 *
	 * @return int Maximum age in hours (default: 24)
	 */
	public function get_max_age()
	{
		return $this->env['RSS_MAX_AGE'] ?? 24;
	}

	/**
	 * Get the post limit
	 *
	 * Returns the maximum number of posts to create per run.
	 *
	 * @return int|false Post limit or false for unlimited
	 */
	public function get_post_limit()
	{
		return $this->env['BLUESKY_POST_LIMIT'] ?? false;
	}

	/**
	 * Check if dry run mode is enabled
	 *
	 * Returns whether the script should run in dry run mode (no actual posts).
	 *
	 * @return bool True if dry run mode is enabled
	 */
	public function is_dry_run()
	{
		return $this->env['DRY_RUN'] ?? false;
	}

	/**
	 * Check if feed title should be shown in posts
	 *
	 * Returns whether the feed title should be displayed at the top of posts.
	 *
	 * @return bool True if feed title should be shown
	 */
	public function show_feed_title()
	{
		return $this->env['SHOW_FEED_TITLE'] ?? false;
	}

	/**
	 * Get the feed title prefix
	 *
	 * Returns the prefix to prepend to feed titles in posts.
	 *
	 * @return string Feed title prefix (default: empty string)
	 */
	public function get_feed_title_prefix()
	{
		return stripcslashes($this->env['FEED_TITLE_PREFIX'] ?? '');
	}

	/**
	 * Get the feed title suffix
	 *
	 * Returns the suffix to append to feed titles in posts.
	 *
	 * @return string Feed title suffix (default: empty string)
	 */
	public function get_feed_title_suffix()
	{
		return stripcslashes($this->env['FEED_TITLE_SUFFIX'] ?? '');
	}
}
