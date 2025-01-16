<?php

class RSS_Reader
{
	private $database_root;
	private $max_age;
	private $post_limit;

	public function __construct($max_age, $post_limit)
	{
		$this->database_root = dirname(__DIR__) . '/database';
		$this->max_age       = $max_age;
		$this->post_limit    = $post_limit;
		$this->create_database();
	}

	public function create_database()
	{
		if (!file_exists($this->database_root . '/posts.sqlite'))
		{
			try
			{
				$db = new SQLite3($this->database_root . '/posts.sqlite');
				$db->exec("CREATE TABLE IF NOT EXISTS posts (
					id INTEGER PRIMARY KEY,
					pub_date TEXT,
					title TEXT,
					link TEXT,
					fingerprint TEXT
				)");
				$db->close();
			}
			catch (Exception $e)
			{
				echo "Databse connection failed: " . $e->getMessage() . PHP_EOL;
			}
		}
	}

	private function get_posts_from_rss($url)
	{
		$rss       = simplexml_load_file($url);
		$posts     = [];
		$rss_items = [];

		if (empty($rss))
		{
			return $posts;
		}


		// RSS 2.0
		if (!empty($rss->channel->item))
		{
			$rss_items = $rss->channel->item;
		}
		// RSS 1.0
		elseif (!empty($rss->item))
		{
			$rss_items = $rss->item;
		}
		// Atom
		elseif (!empty($rss->entry))
		{
			$rss_items = $rss->entry;
		}
		else
		{
			return $posts;
		}

		foreach ($rss_items as $rss_item)
		{
			$title       = (string) $rss_item->title;
			$link        = (string) $rss_item->link;
			$description = !empty((string) $rss_item->description) ? (string) $rss_item->description : (string) $rss_item->content;
			$pub_date    = !empty((string) $rss_item->pubDate) ? (string) $rss_item->pubDate : (string) $rss_item->updated;

			// Clean up description
			$description = trim(strip_tags(html_entity_decode(preg_replace('/\s+/', ' ', $description))));

			// Atom links
			if (empty($link) && $rss_item->link->count() > 0)
			{
				foreach ($rss_item->link as $rss_link)
				{
					if ($rss_link->attributes()->rel == 'alternate')
					{
						$link = (string) $rss_link->attributes()->href;
						break;
					}

				}

				if (empty($link))
				{
					$link = (string) $rss_item->link[0]->attributes()->href;
				}
			}

			$posts[] = [
				'title'       => $title,
				'description' => $description,
				'link'        => $link,
				'pub_date'    => $pub_date,
				'fingerprint' => md5($title . $link)
			];
		}

		return $posts;
	}

	private function get_all_posts($urls)
	{
		$posts = [];

		foreach ($urls as $url)
		{
			$posts = array_merge($this->get_posts_from_rss($url), $posts);
		}

		return $posts;
	}

	public function get_new_posts($urls)
	{
		$posts        = $this->get_all_posts($urls);
		$max_age      = strtotime('-' . $this->max_age . ' hours');
		$post_limit   = $this->post_limit;
		$insert_count = 0;

		if (empty($posts))
		{
			return $posts;
		}

		// Remove posts older than max_age
		$posts = array_filter(
			$posts,

			function ($post) use ($max_age)
			{
				if (empty($post['pub_date']))
				{
					return true;
				}

				return strtotime($post['pub_date']) >= $max_age;
			}
		);

		// Remove posts with the same fingerprint
		$posts = array_reduce(
			$posts,

			function ($carry, $post)
			{
				$carry[$post['fingerprint']] = $post;
				return $carry;
			},
			[]
		);
		$posts = array_values($posts);

		// Sort posts by date ascending
		usort(
			$posts,

			function ($a, $b)
			{
				return strtotime($a['pub_date']) - strtotime($b['pub_date']);
			}
		);

		$db = new SQLite3($this->database_root . '/posts.sqlite');

		// Check if post already exists in database
		foreach ($posts as $key => $post)
		{
			$fingerprint = $db->escapeString($post['fingerprint']);
			$result      = $db->querySingle("SELECT COUNT(*) as count FROM posts WHERE fingerprint = '$fingerprint'");

			if ($result > 0)
			{
				unset($posts[$key]);
			}
			else
			{
				if ($post_limit && $insert_count >= $post_limit)
				{
					unset($posts[$key]);
					continue;
				}

				$pub_date = $db->escapeString($post['pub_date']);
				$title    = $db->escapeString($post['title']);
				$link     = $db->escapeString($post['link']);

				$db->exec("INSERT INTO posts (pub_date, title, link, fingerprint) VALUES ('$pub_date', '$title', '$link', '$fingerprint')");

				$insert_count++;
			}
		}

		$db->close();

		return $posts;
	}
}
