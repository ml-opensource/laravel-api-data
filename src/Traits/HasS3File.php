<?php

namespace Fuzz\Data\Traits;

use Fuzz\ApiServer\Exception\BadRequestException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

trait HasS3File
{
	/**
	 * Base S3 URL
	 *
	 * @var string
	 */
	public static $s3_base_url = 'https://s3.amazonaws.com/';

	/**
	 * Upload a file
	 *
	 * Is this not working? Check your max file upload size in php.ini
	 *
	 * @param \Symfony\Component\HttpFoundation\File\File $file
	 * @param null                                        $path
	 * @param string                                      $visibility
	 * @param bool                                        $random_name_length
	 * @return string
	 * @throws \Fuzz\ApiServer\Exception\BadRequestException
	 */
	public function pushToS3(File $file, $path = null, $visibility = 'private', $random_name_length = false)
	{
		if (! $file->getSize()) {
			throw new BadRequestException('The file could not be processed.', ['Possible issues' => ['The file is too large.', 'The file was not found.']]);
		}

		$filename = $random_name_length ? Str::random($random_name_length) . '.' . $file->guessExtension() :
			$file->getClientOriginalName();

		$key    = $path . $filename;
		$stream = file_get_contents($file->getRealPath());

		Storage::disk('s3')->put($key, $stream, $visibility);

		// Check creation of file
		$new_file = $this->getFileUrl($key);

		// Return the created file's basename
		return basename($new_file);
	}

	/**
	 * Retrieve a file URL
	 *
	 * @param string      $key
	 * @param null|string $path
	 * @return null|string
	 */
	public function getFileUrl($key, $path = null)
	{
		if (is_null($key)) {
			return null;
		}

		$bucket = config('filesystems.disks.s3.bucket');
		$client = Storage::disk('s3')->getAdapter()->getClient();

		return $this->getRealUrl($client->getObjectUrl($bucket, $path . $key));
	}

	/**
	 * Determine if the base S3 url needs to be switched out with a CDN url
	 *
	 * @param string $url
	 * @return mixed
	 */
	public function getRealUrl($url)
	{
		if (config('services.cdn.base_url')) {
			$s3_url = self::$s3_base_url . config('filesystems.disks.s3.bucket') . '/';

			return str_replace($s3_url, config('services.cdn.base_url'), $url);
		} else {
			return $url;
		}
	}
}
