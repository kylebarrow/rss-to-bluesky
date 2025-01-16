<?php

class RSS_To_Bluesky
{
	public function __construct()
	{
		$this->load_classes();
		$this->init();
	}

	private function load_classes()
	{
		require_once 'includes/class-get-env.php';
		require_once 'includes/class-rss-reader.php';
		require_once 'includes/class-bluesky.php';
	}

	private function init()
	{
		$env             = new Get_Env();
		$host            = $env->get_host();
		$handle          = $env->get_handle();
		$app_password    = $env->get_app_password();
		$feeds           = $env->get_feeds();
		$languages       = $env->get_post_languages();
		$max_age         = $env->get_max_age();
		$post_limit      = $env->get_post_limit();
		$dry_run         = $env->is_dry_run();
		$posts_processed = 0;
		$posts_posted    = 0;

		$rss_reader = new RSS_Reader($max_age, $post_limit);
		$bluesky    = new Bluesky($host, $handle, $app_password);
		$rss_posts  = $rss_reader->get_new_posts($feeds);

		if (empty($rss_posts))
		{
			echo 'No new posts' . PHP_EOL;
			return;
		}

		echo $dry_run ? '================== DRY RUN ==================' . PHP_EOL : '';

		foreach ($rss_posts as $rss_post)
		{
			$posts_processed++;
			echo 'Processing RSS post' . PHP_EOL;
			$response = $bluesky->create_post($rss_post, $languages, $dry_run);

			if ($response)
			{
				$posts_posted++;
				echo 'Posted RSS post' . PHP_EOL;
			}
		}

		echo PHP_EOL . 'RSS posts processed: ' . $posts_processed . PHP_EOL . 'RSS posts posted: ' . $posts_posted . PHP_EOL;
	}
}
new RSS_To_Bluesky();
