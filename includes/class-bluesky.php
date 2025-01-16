<?php

class Bluesky
{
	private const MAX_IMAGE_SIZE = 1000000;
	private const MAX_POST_SIZE  = 300;
	private $host         = null;
	private $handle       = null;
	private $access_token = null;
	private $did          = null;

	public function __construct($host, $handle, $password)
	{
		$this->host   = $host;
		$this->handle = $handle;
		$credentials  = $this->get_credentials($host, $handle, $password);

		if ($credentials)
		{
			$this->access_token = $credentials->accessJwt;
			$this->did          = $credentials->did;
		}
	}

	private function get_best_text(...$variables)
	{
		foreach ($variables as $variable)
		{
			if (!empty($variable))
			{
				return $variable;
			}
		}
		return '';
	}

	private function get_cache_key($host, $handle, $password)
	{
		return 'bluesky_credentials_' . md5($handle . $host . $password);
	}

	private function get_cached_credentials($host, $handle, $password)
	{
		if (class_exists('Memcached'))
		{
			$memcached = new Memcached();

			if ($memcached->addServer('localhost', 11211))
			{
				$cache_key   = $this->get_cache_key($host, $handle, $password);
				$credentials = $memcached->get($cache_key);

				if (empty($credentials))
				{
					return false;
				}

				return json_decode($credentials);
			}
		}

		return false;
	}

	private function set_cached_credentials($host, $handle, $password, $credentials)
	{
		if (class_exists('Memcached'))
		{
			$memcached = new Memcached();

			if ($memcached->addServer('localhost', 11211))
			{
				echo 'Caching credentials for ' . $handle . PHP_EOL;

				$cache_key = $this->get_cache_key($host, $handle, $password);
				return $memcached->set($cache_key, json_encode($credentials), time() + 600); // Cache for 10 minutes
			}
		}
	}

	private function get_refreshed_credentials($host, $handle, $password, $credentials)
	{
		echo 'Refreshing credentials for ' . $handle . PHP_EOL;
		$options = [
			'http' => [
				'header' => ['Content-type: application/json', 'Authorization: Bearer ' . $credentials->refreshJwt],
				'method' => 'POST'
			],
		];

		$endpoint = $host . '/xrpc/com.atproto.server.refreshSession';
		$context  = stream_context_create($options);
		$response = file_get_contents($endpoint, false, $context);

		if (empty($response))
		{
			echo 'No com.atproto.server.refreshSession response from Bluesky' . PHP_EOL;
			return $credentials;
		}

		$response = json_decode($response);

		if (!empty($response->error))
		{
			echo 'Bluesky com.atproto.server.refreshSession error: ' . $response->error . PHP_EOL;
			return $credentials;
		}

		// Remove unnecessary data
		$response = (object) [
			'accessJwt'  => $response->accessJwt,
			'refreshJwt' => $response->refreshJwt,
			'did'        => $response->did
		];

		$this->set_cached_credentials($host, $handle, $password, $response);

		return $response;
	}

	private function get_credentials($host, $handle, $password)
	{
		$credentials = $this->get_cached_credentials($host, $handle, $password);

		if ($credentials)
		{
			echo 'Found cached credentials for ' . $handle . PHP_EOL;

			return $this->get_refreshed_credentials($host, $handle, $password, $credentials);
		}

		echo 'Getting new credentials for ' . $handle . PHP_EOL;

		$endpoint = $host . '/xrpc/com.atproto.server.createSession';
		$data     = [
			'identifier' => $handle,
			'password'   => $password
		];

		$options = [
			'http' => [
				'header'  => 'Content-type: application/json',
				'method'  => 'POST',
				'content' => json_encode($data)
			],
		];

		$context  = stream_context_create($options);
		$response = file_get_contents($endpoint, false, $context);

		if (empty($response))
		{
			echo 'No com.atproto.server.createSession response from Bluesky' . PHP_EOL;
			return false;
		}

		$response = json_decode($response);

		if (!empty($response->error))
		{
			echo 'Bluesky com.atproto.server.createSession error: ' . $response->error . PHP_EOL;
			return false;
		}

		// Remove unnecessary data
		$response = (object) [
			'accessJwt'  => $response->accessJwt,
			'refreshJwt' => $response->refreshJwt,
			'did'        => $response->did
		];

		$this->set_cached_credentials($host, $handle, $password, $response);

		return $response;
	}

	private function get_open_graph_data($url)
	{
		if (empty($url))
		{
			echo 'No URL provided for open graph data' . PHP_EOL;
			return [];
		}

		echo 'Searching for open graph data at ' . $url . PHP_EOL;

		$document = new DOMDocument();

		libxml_use_internal_errors(true);
		$document->loadHTMLFile($url);
		libxml_clear_errors();

		$xpath     = new DOMXPath($document);
		$meta_tags = $xpath->query("//meta[starts-with(@property, 'og:')]");
		$data      = [];

		if (empty($meta_tags))
		{
			echo 'No open graph data found' . PHP_EOL;
			return $data;
		}

		echo 'Found open graph data' . PHP_EOL;

		foreach ($meta_tags as $meta_tag)
		{
			$property        = $meta_tag->getAttribute('property');
			$content         = $meta_tag->getAttribute('content');
			$data[$property] = $content;
		}

		return $data;
	}

	private function downsize_image($image_blob)
	{
		echo 'Downsizing image' . PHP_EOL;

		$image  = imagecreatefromstring($image_blob);
		$width  = imagesx($image);
		$height = imagesy($image);

		do
		{
			$downsize_width  = $width * 0.8;
			$downsize_height = $height * 0.8;

			$downsized_image = imagescale($image, $downsize_width, $downsize_height);
			ob_start();
			imagejpeg($downsized_image);
			$image_blob = ob_get_clean();

			$width  = $downsize_width;
			$height = $downsize_height;

			imagedestroy($downsized_image);
		} while (strlen($image_blob) > self::MAX_IMAGE_SIZE);

		imagedestroy($image);

		return $image_blob;
	}

	private function upload_image($url)
	{
		echo 'Uploading image ' . $url . PHP_EOL;

		$image_blob   = file_get_contents($url);
		$content_type = null;

		if (empty($image_blob))
		{
			echo 'No image blob returned' . PHP_EOL;
			return false;
		}

		preg_match('/^content-type\s*:\s*(.*)$/mi', implode(PHP_EOL, $http_response_header), $header_matches);

		if (empty($header_matches[1]))
		{
			echo 'No content type found' . PHP_EOL;
			return false;
		}

		$content_type   = $header_matches[1];
		$content_length = strlen($image_blob);

		if ($content_length > self::MAX_IMAGE_SIZE)
		{
			$image_blob   = $this->downsize_image($image_blob);
			$content_type = 'image/jpeg';
		}

		$endpoint = $this->host . '/xrpc/com.atproto.repo.uploadBlob';
		$options  = [
			'http' => [
				'header'  => ['Content-type: ' . $content_type, 'Authorization: Bearer ' . $this->access_token],
				'method'  => 'POST',
				'content' => $image_blob
			],
		];

		$context  = stream_context_create($options);
		$response = file_get_contents($endpoint, false, $context);

		if (empty($response))
		{
			echo 'No com.atproto.repo.uploadBlob response from Bluesky' . PHP_EOL;
			return false;
		}

		$response = json_decode($response);

		if (!empty($response->error))
		{
			echo 'Bluesky com.atproto.repo.uploadBlob error: ' . $response->error . PHP_EOL;
			return false;
		}

		return $response->blob;
	}

	public function create_post($rss_post, $languages, $dry_run = false)
	{
		if (empty($this->access_token) || empty($this->did))
		{
			echo 'No Bluesky access token or DID found' . PHP_EOL;
			return false;
		}

		if (empty($rss_post['title']) && empty($rss_post['description']) && empty($rss_post['link']))
		{
			echo 'No title, description, or link found for post' . PHP_EOL;
			return false;
		}

		$open_graph_data = $this->get_open_graph_data($rss_post['link']);

		$text              = $this->get_best_text($rss_post['title'], $open_graph_data['og:title'] ?? null, $open_graph_data['og:description'] ?? null, $rss_post['description'], $rss_post['link']);
		$embed_title       = $this->get_best_text($open_graph_data['og:title'] ?? null, $rss_post['title']);
		$embed_description = $open_graph_data['og:description'] ?? '';
		$link              = $rss_post['link'];
		$created_at        = !empty($rss_post['pub_date']) ? $rss_post['pub_date'] : gmdate('Y-m-d\TH:i:s.v\Z', time());


		if (strlen($text) > self::MAX_POST_SIZE)
		{
			echo 'Title length exceeded Bluesky post limit, shortened' . PHP_EOL;
			$text = substr($text, 0, self::MAX_POST_SIZE - 1) . '…';
		}


		$post = [
			'collection' => 'app.bsky.feed.post',
			'repo'       => $this->did,
			'record'     => [
				'text'      => $text,
				'langs'     => $languages,
				'createdAt' => $created_at,
				'$type'     => 'app.bsky.feed.post'
			]
		];

		if (!empty($link))
		{
			$post['record']['embed'] = [
				'$type'    => 'app.bsky.embed.external',
				'external' => [
					'uri'         => $link,
					'title'       => $embed_title,
					'description' => $embed_description
				]

			];

			if (!empty($open_graph_data['og:image']))
			{
				$image = $this->upload_image($open_graph_data['og:image']);

				if (!empty($image))
				{
					$post['record']['embed']['external']['thumb'] = $image;
				}
			}
		}


		if ($dry_run)
		{
			echo 'Dry run post data:' . PHP_EOL . json_encode($post, JSON_PRETTY_PRINT) . PHP_EOL;
			return false;
		}

		echo 'Posting ' . $rss_post['link'] . PHP_EOL;

		$endpoint = $this->host . '/xrpc/com.atproto.repo.createRecord';
		$options  = [
			'http' => [
				'header'  => ['Content-type: application/json', 'Authorization: Bearer ' . $this->access_token],
				'method'  => 'POST',
				'content' => json_encode($post)
			],
		];

		$context  = stream_context_create($options);
		$response = file_get_contents($endpoint, false, $context);

		if (empty($response))
		{
			echo 'No com.atproto.repo.createRecord response from Bluesky' . PHP_EOL;
			return false;
		}

		$response = json_decode($response);

		if (!empty($response->error))
		{
			echo 'Bluesky com.atproto.repo.createRecord error: ' . $response->error . PHP_EOL;
			return false;
		}

		return $response;
	}
}
