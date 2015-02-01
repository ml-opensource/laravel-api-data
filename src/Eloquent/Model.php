<?php

namespace Fuzz\Data\Eloquent;

use Carbon\Carbon;
use Fuzz\File\File;
use InvalidArgumentException;
use Fuzz\LaravelS3\Facades\S3Manager;
use Illuminate\Support\Facades\Input;
use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Model extends Eloquent
{
	/**
	 * The directory where assets are uploaded. Frequently overridden with late static binding.
	 *
	 * @var string
	 */
	const ASSET_DIRECTORY = 'assets';

	/**
	 * The directory where images are uploaded. Frequently overridden with late static binding.
	 *
	 * @var string
	 */
	const IMAGE_DIRECTORY = 'images';

	/**
	 * Storage for required fields.
	 *
	 * @var array
	 */
	public static $required_fields = [];

	/**
	 * Add additional appended properties to the model via a public interface.
	 *
	 * @param string|array $appends
	 * @return static
	 */
	public function addAppends($appends)
	{
		if (is_string($appends)) {
			$appends = func_get_args();
		}

		$this->appends = array_merge($this->appends, $appends);

		return $this;
	}

	/**
	 * Unhide hidden properties from the model via a public interface.
	 *
	 * @param string|array $appends
	 * @return static
	 */
	public function removeHidden($hidden)
	{
		if (is_string($hidden)) {
			$hidden = func_get_args();
		}

		$this->hidden = array_diff($this->hidden, $hidden);

		return $this;
	}

	/**
	 * Mutator for image attributes.
	 *
	 * @param  string|File $input_variable
	 *         Either a file input name, a local file path, a web URL to a valid image,
	 *         or a constructed File object
	 * @param  string $attribute
	 *         The name of the attribute we're setting
	 * @param  string $crops
	 *         An optional crop spec to crop to
	 * @return string
	 */
	final protected function mutateImageAttribute($input_variable, $attribute, $crops = false)
	{
		// If the input variable is already an MD5, assume it is already on S3 with reasonable certainty
		if (! (is_string($input_variable) && preg_match('#[0-9a-f]{32}(_\w+)?\.[a-z]{3,4}$#', $input_variable))) {
			$image_directory = static::getImageDirectory();

			// Accept constructed File objects
			if (is_object($input_variable) && $input_variable instanceof File) {
				$file = S3Manager::uploadImageFileObject($input_variable, $image_directory, $crops);
			// Accept multipart file uploads exposed to PHP
			} elseif (Input::hasFile($input_variable)) {
				$file = S3Manager::uploadImage($input_variable, $image_directory, $crops);
			// Accept local file paths
			} elseif (file_exists($input_variable)) {
				$file = S3Manager::uploadImageFile($input_variable, $image_directory, $crops);
			// Accept fully qualified URLs to images
			} elseif (filter_var(
				$input_variable,
				FILTER_VALIDATE_URL,
				FILTER_FLAG_SCHEME_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_PATH_REQUIRED
			)) {
				$file = S3Manager::uploadImageBlob(file_get_contents($input_variable), $image_directory, $crops);
			}

			// Reject nonimages or unparseable input
			if (! isset($file) || ! $file->isImage()) {
				return false;
			}

			// Set the value of the file to the unqualified URL of the filename
			return $this->attributes[$attribute] = $file->getFullFilename();
		}

		// Use basename to ensure we are not embedding S3 URLs in the database on re-save
		return $this->attributes[$attribute] = basename($input_variable);
	}

	/**
	 * Accessor for image attributes.
	 *
	 * @param  string $filename
	 *         The name of the file in storage
	 * @return string
	 *         The fully qualified S3 URL to the image
	 */
	final protected function accessImageAttribute($filename, $crop = null)
	{
		if (empty($filename)) {
			return null;
		}

		if (! is_null($crop)) {
			$path_parts = pathinfo($filename);
			$filename = @$path_parts['filename'] . '_' . $crop . '.' . $path_parts['extension'];
		}

		return S3Manager::getUrl($filename, static::getImageDirectory(), true);
	}

	/**
	 * Mutator for datetime attributes.
	 *
	 * @param string/int $date
	 *        A parsable datetime string or timestamp
	 * @param string $attribute
	 *        The name of the attribute we're setting
	 * @return \Carbon\Carbon
	 */
	final protected function mutateDateTimeAttribute($date, $attribute)
	{
		if (is_numeric($date)) {
			return $this->attributes[$attribute] = Carbon::createFromTimestamp($date);
		}

		return $this->attributes[$attribute] = Carbon::parse($date)->toDateTimeString();
	}

	/**
	 * Accessor for datetime attributes. Ensures we always send datetimes as UNIX timestamps.
	 *
	 * @param string $value
	 *        A datetime value
	 * @return int
	 */
	final protected function accessDateTimeAttribute($value)
	{
		return strtotime($value);
	}

	/**
	 * Return an Fuzz\S3\Conveyor-compatible array of directory keys for storing
	 * and retrieving images. With late static binding, we can arbitrarily override
	 * the directory components used here.
	 *
	 * @param  string $subdirectory an optional subdirectory
	 * @return array  a collection of subdirectory components
	 */
	public static function getImageDirectory($subdirectory = null)
	{
		return array(
			static::ASSET_DIRECTORY,
			$subdirectory,
			static::IMAGE_DIRECTORY
		);
	}

	/**
	 * Return a string-representation of the Fuzz\S3\Conveyor-compatible array
	 * of directory keys for storing and retrieving images.
	 *
	 * @param  string $subdirectory an optional subdirectory
	 * @return string a string representing the path to the image directory
	 */
	public static function getImageDirectoryString($subdirectory = null)
	{
		$directory_parts = static::getImageDirectory();
		return implode('/', array_filter($directory_parts));
	}

	/**
	 * Starter query for index presentation of data.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public static function indexQuery()
	{
		return static::query();
	}
}
