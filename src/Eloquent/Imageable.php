<?php

namespace Fuzz\Data\Eloquent;

use Fuzz\File\File;
use Fuzz\LaravelS3\Facades\S3Manager;
use Illuminate\Support\Facades\Input;

trait Imageable
{
	/**
	 * Return an Fuzz\S3\Conveyor-compatible array of directory keys for storing
	 * and retrieving images. With late static binding, we can arbitrarily override
	 * the directory components used here.
	 *
	 * @param string|null $context
	 * @return array  a collection of subdirectory components
	 */
	abstract protected function getImageDirectory($context = null);

	/**
	 * Return a string-representation of the Fuzz\S3\Conveyor-compatible array
	 * of directory keys for storing and retrieving images.
	 *
	 * @param string|null $context
	 * @return string a string representing the path to the image directory
	 */
	protected function getImageDirectoryString($context = null)
	{
		return implode('/', array_filter($this->getImageDirectory($context)));
	}

	/**
	 * Accessor for image attributes.
	 *
	 * @param  string     $filename
	 *         The name of the file in storage
	 * @param string|null $context
	 * @return string
	 *         The fully qualified S3 URL to the image
	 */
	final protected function accessImageAttribute($filename, $context = null)
	{
		if (empty($filename)) {
			return null;
		}

		return S3Manager::getUrl($filename, $this->getImageDirectory($context), true);
	}

	/**
	 * Mutator for image attributes.
	 *
	 * @param  string|File $input_variable
	 *         Either a file input name, a local file path, a web URL to a valid image,
	 *         or a constructed File object
	 * @param  string      $attribute
	 *         The name of the attribute we're setting
	 * @param  string      $crops
	 *         An optional crop spec to crop to
	 * @return string
	 */
	final protected function mutateImageAttribute($input_variable, $attribute, $crops = false, $context = null)
	{
		// If the input variable is already an MD5, assume it is already on S3 with reasonable certainty
		$image_directory_string = $this->getImageDirectoryString($context);
		if (! (is_string($input_variable) && preg_match(sprintf('#%s/[0-9a-f]{32}(_\w+)?\.[a-z]{3,4}$#', $image_directory_string), $input_variable))) {
			$image_directory = $this->getImageDirectory($context);

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
				$input_variable, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED & FILTER_FLAG_HOST_REQUIRED & FILTER_FLAG_PATH_REQUIRED
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
}
