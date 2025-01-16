<?php

class Get_Env
{
	private $env_root;
	private $env      = [];

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

	public function get_host()
	{
		return $this->env['BLUESKY_HOST'] ?? 'https://bsky.social';
	}

	public function get_handle()
	{
		if (empty($this->env['BLUESKY_HANDLE']))
		{
			echo 'No handle found in .env' . PHP_EOL;
			return null;
		}

		return $this->env['BLUESKY_HANDLE'];
	}

	public function get_app_password()
	{
		if (empty($this->env['BLUESKY_APP_PASSWORD']))
		{
			echo 'No app password in .env' . PHP_EOL;
			return null;
		}

		return $this->env['BLUESKY_APP_PASSWORD'];
	}

	public function get_feeds()
	{
		if (empty($this->env['RSS_FEEDS']))
		{
			echo 'No feeds found in .env' . PHP_EOL;
			return [];
		}

		return array_map('trim', explode(',', $this->env['RSS_FEEDS']));
	}

	public function get_post_languages()
	{
		if (empty($this->env['BLUESKY_POST_LANGUAGES']))
		{
			return ['en'];
		}

		return array_map('trim', explode(',', $this->env['BLUESKY_POST_LANGUAGES']));
	}

	public function get_max_age()
	{
		return $this->env['RSS_MAX_AGE'] ?? 24;
	}

	public function get_post_limit()
	{
		return $this->env['BLUESKY_POST_LIMIT'] ?? false;
	}

	public function is_dry_run()
	{
		return $this->env['DRY_RUN'] ?? false;
	}
}
