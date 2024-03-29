<?php
/*
 * Copyright 2011 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */


/**
 * Provides an interface for accessing Amazon S3 using PHP's native file management functions. This class is not
 * auto-loaded, and must be included manually.
 *
 * Amazon S3 file patterns take the following form: <code>s3://bucket/object</code>
 */
class S3StreamWrapper extends AmazonS3
{
	/*%******************************************************************************************%*/
	// CONSTANTS

	const REGION_US_E1 = 1;
	const REGION_US_W1 = 2;
	const REGION_US_W2 = 3;
	const REGION_EU_W1 = 4;
	const REGION_APAC_NE1 = 5;
	const REGION_APAC_SE1 = 6;
	const REGION_SA_E1 = 7;
	const REGION_US_GOV1 = 8;
	const REGION_US_GOV1_FIPS = 9;

	const REGION_US_STANDARD = self::REGION_US_W1;
	const REGION_VIRGINIA = self::REGION_US_W1;
	const REGION_CALIFORNIA = self::REGION_US_W1;
	const REGION_OREGON = self::REGION_US_W2;
	const REGION_IRELAND = self::REGION_EU_W1;
	const REGION_SINGAPORE = self::REGION_APAC_SE1;
	const REGION_TOKYO = self::REGION_APAC_NE1;
	const REGION_SAO_PAULO = self::REGION_SA_E1;


	/*%******************************************************************************************%*/
	// PROPERTIES

	public $context = null;
	public $position = 0;
	public $path = null;
	public $file_list = null;
	public $open_file = null;
	public $seek_position = 0;
	public $eof = false;


	/*%******************************************************************************************%*/
	// STREAM WRAPPER IMPLEMENTATION

	/**
	 * Constructs a new instance of <S3StreamWrapper>.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Close directory handle. This method is called in response to <php:closedir()>.
	 *
	 * Since Amazon S3 doesn't have real directories, always return <code>true</code>.
	 *
	 * @return boolean
	 */
	public function dir_closedir()
	{
		$this->context = null;
		$this->position = 0;
		$this->path = null;
		$this->file_list = null;
		$this->open_file = null;
		$this->seek_position = 0;
		$this->eof = false;

		return true;
	}

	/**
	 * Open directory handle. This method is called in response to <php:opendir()>.
	 *
	 * @param string $path (Required) Specifies the URL that was passed to <php:opendir()>.
	 * @param integer $options (Required) Not used. Passed in by <php:opendir()>.
	 * @return boolean Returns <code>true</code> on success or <code>false</code> on failure.
	 */
	public function dir_opendir($path, $options)
	{
		self::__construct();

		$url = parse_url($path);
		$this->path = $path;
		$bucket_name = $url['host'];
		$path_name = (isset($url['path']) ? $url['path'] : '');

		$pattern = '/^' . self::regex_token(substr($path_name, 1)) . '(.*)[^\/$]/';

		$this->file_list = $this->get_object_list($bucket_name, array(
			'pcre' => $pattern
		));

		return (count($this->file_list)) ? true : false;
	}

	/**
	 * This method is called in response to <php:readdir()>.
	 *
	 * @return string Should return a string representing the next filename, or <code>false</code> if there is no next file.
	 */
	public function dir_readdir()
	{
		self::__construct();

		if (isset($this->file_list[$this->position]))
		{
			$out = $this->file_list[$this->position];
			$this->position++;
		}
		else
		{
			$out = false;
		}

		return $out;
	}

	/**
	 * This method is called in response to <php:rewinddir()>.
	 *
	 * Should reset the output generated by <php:streamWrapper::dir_readdir()>. i.e.: The next call to
	 * <php:streamWrapper::dir_readdir()> should return the first entry in the location returned by
	 * <php:streamWrapper::dir_opendir()>.
	 *
	 * @return boolean Returns <code>true</code> on success or <code>false</code> on failure.
	 */
	public function dir_rewinddir()
	{
		$this->position = 0;
		return true;
	}

	/**
	 * Create a new bucket. This method is called in response to <php:mkdir()>.
	 *
	 * @param string $path (Required) The bucket name to create.
	 * @param integer $mode (Optional) Permissions. 700-range permissions map to ACL_PUBLIC. 600-range permissions map to ACL_AUTH_READ. All other permissions map to ACL_PRIVATE.
	 * @param integer $options (Optional) Regions. [Allowed values: `S3StreamWrapper::REGION_US_E1`, `S3StreamWrapper::REGION_US_W1`, `S3StreamWrapper::REGION_EU_W1`, `S3StreamWrapper::REGION_APAC_NE1`, `S3StreamWrapper::REGION_APAC_SE1`]
	 * @return boolean Whether the bucket was created successfully or not.
	 */
	public function mkdir($path, $mode = 0, $options = 1)
	{
		self::__construct();

		$url = parse_url($path);
		$this->path = $path;
		$bucket_name = $url['host'];

		switch ($mode)
		{
			// 700-range permissions
			case in_array((integer) $mode, range(700, 799)):
				$acl = AmazonS3::ACL_PUBLIC;
				break;

			// 600-range permissions
			case in_array((integer) $mode, range(600, 699)):
				$acl = AmazonS3::ACL_AUTH_READ;
				break;

			// All other permissions
			default:
				$acl = AmazonS3::ACL_PRIVATE;
				break;
		}

		switch ($options)
		{
			case self::REGION_US_W1:
			case self::REGION_CALIFORNIA:
				$region = AmazonS3::REGION_CALIFORNIA;
				break;

			case self::REGION_US_W2:
			case self::REGION_OREGON:
				$region = AmazonS3::REGION_OREGON;
				break;

			case self::REGION_EU_W1:
			case self::REGION_IRELAND:
				$region = AmazonS3::REGION_IRELAND;
				break;

			case self::REGION_APAC_NE1:
			case self::REGION_TOKYO:
				$region = AmazonS3::REGION_TOKYO;
				break;

			case self::REGION_APAC_SE1:
			case self::REGION_SINGAPORE:
				$region = AmazonS3::REGION_SINGAPORE;
				break;

			case self::REGION_SA_E1:
			case self::REGION_SAO_PAULO:
				$region = AmazonS3::REGION_SAO_PAULO;
				break;

			case self::REGION_US_GOV1:
				$region = AmazonS3::REGION_US_GOV1;
				break;

			case self::REGION_US_GOV1_FIPS:
				$region = AmazonS3::REGION_US_GOV1_FIPS;
				break;

			case self::REGION_US_E1:
			case self::REGION_US_STANDARD:
			case self::REGION_VIRGINIA:
			default:
				$region = AmazonS3::REGION_US_STANDARD;
				break;
		}

		$response = $this->create_bucket($bucket_name, $region, $acl);
		return $response->isOK();
	}

	/**
	 * Renames a file or directory. This method is called in response to <php:rename()>.
	 *
	 * @param string $path_from (Required) The URL to the current file.
	 * @param string $path_to (Required) The URL which the <code>$path_from</code> should be renamed to.
	 * @return boolean Returns <code>true</code> on success or <code>false</code> on failure.
	 */
	public function rename($path_from, $path_to)
	{
		self::__construct();

		$from_url = parse_url($path_from);
		$from_bucket_name = $from_url['host'];
		$from_object_name = substr($from_url['path'], 1);

		$to_url = parse_url($path_to);
		$to_bucket_name = $to_url['host'];
		$to_object_name = substr($to_url['path'], 1);

		$copy_response = $this->copy_object(
			array('bucket' => $from_bucket_name, 'filename' => $from_object_name),
			array('bucket' => $to_bucket_name,   'filename' => $to_object_name  )
		);

		if ($copy_response->isOK())
		{
			$delete_response = $this->delete_object($from_bucket_name, $from_object_name);

			if ($delete_response->isOK())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * This method is called in response to <php:rmdir()>.
	 *
	 * @param string $bucket (Required) The bucket name to create.
	 * @param boolean $force (Optional) Whether to force-delete the bucket or not. The default value is <code>false</code>.
	 * @return boolean Whether the bucket was deleted successfully or not.
	 */
	public function rmdir($path, $force = false)
	{
		self::__construct();

		$url = parse_url($path);
		$this->path = $path;
		$bucket_name = $url['host'];

		$response = $this->delete_bucket($bucket_name, $force);
		return $response->isOK();
	}

	/**
	 * NOT IMPLEMENTED!
	 *
	 * @param integer $cast_as
	 * @return resource
	 */
	// public function stream_cast($cast_as) {}

	/**
	 * Close a resource. This method is called in response to <php:fclose()>.
	 *
	 * All resources that were locked, or allocated, by the wrapper should be released.
	 *
	 * @return void
	 */
	public function stream_close()
	{
		$this->context = null;
		$this->position = 0;
		$this->path = null;
		$this->file_list = null;
		$this->open_file = null;
		$this->seek_position = 0;
		$this->eof = false;
	}

	/**
	 * Tests for end-of-file on a file pointer. This method is called in response to <php:feof()>.
	 *
	 * @return boolean
	 */
	public function stream_eof()
	{
		return $this->eof;
	}

	/**
	 * Flushes the output. This method is called in response to <php:fflush()>. If you have cached data in
	 * your stream but not yet stored it into the underlying storage, you should do so now.
	 *
	 * Since this implementation doesn't buffer streams, simply return <code>true</code>.
	 *
	 * @return boolean <code>true</code>
	 */
	public function stream_flush()
	{
		return true;
	}

	/**
	 * This method is called in response to <php:flock()>, when <php:file_put_contents()> (when flags contains
	 * <code>LOCK_EX</code>), <php:stream_set_blocking()> and when closing the stream (<code>LOCK_UN</code>).
	 *
	 * Not implemented in S3, so it's not implemented here.
	 *
	 * @param mode $operation
	 * @return boolean
	 */
	// public function stream_lock($operation) {}

	/**
	 * Opens file or URL. This method is called immediately after the wrapper is initialized
	 * (e.g., by <php:fopen()> and <php:file_get_contents()>).
	 *
	 * @param string $path (Required) Specifies the URL that was passed to the original function.
	 * @param string $mode (Required) Ignored.
	 * @param integer $options (Required) Ignored.
	 * @param string &$opened_path (Required) Returns the same value as was passed into <code>$path</code>.
	 * @return boolean Returns <code>true</code> on success or <code>false</code> on failure.
	 */
	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$opened_path = $this->open_file = $path;
		$url = parse_url($path);
		$this->path = $path;
		$bucket_name = $url['host'];
		$path_name = $url['path'];
		$this->seek_position = 0;

		return true;
	}

	/**
	 * Read from stream. This method is called in response to <php:fread()> and <php:fgets()>.
	 *
	 * The documentation for <php:fread()> states: "depending on the previously buffered data, the size of
	 * the returned data may be larger than the chunk size." In this implementation, the <code>$count</code>
	 * parameter is ignored, as the entire stream will be returned at once.
	 *
	 * It is important to avoid reading files that are larger than the amount of memory allocated to PHP,
	 * otherwise "out of memory" errors will occur.
	 *
	 * @param integer $count (Required) Ignored.
	 * @return string The contents of the Amazon S3 object.
	 */
	public function stream_read($count)
	{
		self::__construct();

		$url = parse_url($this->path);
		$bucket_name = $url['host'];
		$path_name = $url['path'];

		if ($this->seek_position !== 0)
		{
			$response = $this->get_object($bucket_name, substr($path_name, 1), array(
				'range' => $this->seek_position . '-'
			));
		}
		else
		{
			$response = $this->get_object($bucket_name, substr($path_name, 1));
		}

		$this->seek_position = isset($response->header['content-length']) ? $response->header['content-length'] : 0;
		$this->eof = true;

		return $response->body;
	}

	/**
	 * Seeks to specific location in a stream. This method is called in response to <php:fseek()>. The read/write
	 * position of the stream should be updated according to the <code>$offset</code> and <code>$whence</code>
	 * parameters.
	 *
	 * @param integer $offset (Required) The number of bytes to offset from the start of the file.
	 * @param integer $whence (Optional) Ignored. Always uses <code>SEEK_SET</code>.
	 * @return boolean Whether or not the seek was successful.
	 */
	public function stream_seek($offset, $whence = SEEK_SET)
	{
		$this->seek_position = $offset;
		return true;
	}

	/**
	 * @param integer $option
	 * @param integer $arg1
	 * @param integer $arg2
	 * @return boolean
	 */
	// public function stream_set_option($option, $arg1, $arg2) {}

	/**
	 * Retrieve information about a file resource.
	 *
	 * @return array Returns the same data as a call to <php:stat()>.
	 */
	public function stream_stat()
	{
		return $this->url_stat($this->path, null);
	}

	/**
	 * Retrieve the current position of a stream. This method is called in response to <php:ftell()>.
	 *
	 * @return integer Returns the current position of the stream.
	 */
	public function stream_tell()
	{
		return $this->seek_position;
	}

	/**
	 * Write to stream. This method is called in response to <php:fwrite()>.
	 *
	 * It is important to avoid reading files that are larger than the amount of memory allocated to PHP,
	 * otherwise "out of memory" errors will occur.
	 *
	 * @param string $data (Required) The data to write to the stream.
	 * @return integer The number of bytes that were written to the stream.
	 */
	public function stream_write($data)
	{
		self::__construct();

		$url = parse_url($this->path);
		$bucket_name = $url['host'];
		$path_name = $url['path'];

		if ($this->seek_position !== 0)
		{
			trigger_error(__CLASS__ . ' is unable to append to a file, so the seek position for this resource must be rewound to position 0.');
		}
		else
		{
			$response = $this->create_object($bucket_name, substr($path_name, 1), array(
				'body' => $data
			));
		}

		$this->seek_position = $response->header['x-aws-requestheaders']['Content-Length'];
		$this->eof = true;

		return $this->seek_position;
	}

	/**
	 * Delete a file. This method is called in response to <php:unlink()>.
	 *
	 * @param string $path (Required) The file URL which should be deleted.
	 * @return boolean Returns <code>true</code> on success or <code>false</code> on failure.
	 */
	public function unlink($path)
	{
		self::__construct();

		$url = parse_url($path);
		$this->path = $path;
		$bucket_name = $url['host'];
		$path_name = $url['path'];

		$response = $this->delete_object($bucket_name, substr($path_name, 1));

		return $response->isOK();
	}

	/**
	 * This method is called in response to all <php:stat()> related functions.
	 *
	 * @param string $path (Required) The file path or URL to stat. Note that in the case of a URL, it must be a <code>://</code> delimited URL. Other URL forms are not supported.
	 * @param integer $flags (Required) Holds additional flags set by the streams API. This implementation ignores all defined flags.
	 * @return array Should return as many elements as <php:stat()> does. Unknown or unavailable values should be set to a rational value (usually <code>0</code>).
	 */
	public function url_stat($path, $flags)
	{
		self::__construct();

		$url = parse_url($path);
		$this->path = $path;
		$bucket_name = (isset($url['host']) ? $url['host'] : '');
		$path_name = ((isset($url['path']) && $url['path'] !== '/') ? $url['path'] : null);
		$file = null;
		$mode = 0;

		if ($path_name)
		{
			$list = $this->list_objects($bucket_name, array(
				'prefix' => substr($path_name, 1)
			))->body;

			$file = $list->Contents[0];
		}
		else
		{
			$list = $this->list_objects($bucket_name)->body;
		}

		/*
		Type & Permission bitwise values (only those that pertain to S3).
		Simulate the concept of a "directory". Nothing has an executable bit because there's no executing on S3.
		Reference: http://docstore.mik.ua/orelly/webprog/pcook/ch19_13.htm

		0100000 => type:   regular file
		0040000 => type:   directory
		0000400 => owner:  read permission
		0000200 => owner:  write permission
		0000040 => group:  read permission
		0000020 => group:  write permission
		0000004 => others: read permission
		0000002 => others: write permission
		*/

		// File or directory?
		// @todo: Add more detailed support for permissions. Currently only takes OWNER into account.
		if (!$path_name) // Root of the bucket
		{
			$mode = octdec('0040777');
		}
		elseif ($file)
		{
			$mode = (str_replace('//', '/', substr($path_name, 1) . '/') === (string) $file->Key) ? octdec('0040777') : octdec('0100777'); // Directory, Owner R/W : Regular File, Owner R/W
		}
		else
		{
			$mode = octdec('0100777');
		}

		$out = array();
		$out[0] = $out['dev'] = 0;
		$out[1] = $out['ino'] = 0;
		$out[2] = $out['mode'] = $mode;
		$out[3] = $out['nlink'] = 0;
		$out[4] = $out['uid'] = (isset($file) ? (string) $file->Owner->ID : 0);
		$out[5] = $out['gid'] = 0;
		$out[6] = $out['rdev'] = 0;
		$out[7] = $out['size'] = (isset($file) ? (string) $file->Size : 0);
		$out[8] = $out['atime'] = (isset($file) ? date('U', strtotime((string) $file->LastModified)) : 0);
		$out[9] = $out['mtime'] = (isset($file) ? date('U', strtotime((string) $file->LastModified)) : 0);
		$out[10] = $out['ctime'] = (isset($file) ? date('U', strtotime((string) $file->LastModified)) : 0);
		$out[11] = $out['blksize'] = 0;
		$out[12] = $out['blocks'] = 0;

		return $out;
	}


	/*%******************************************************************************************%*/
	// HELPERS

	/**
	 * Makes the given token PCRE-compatible.
	 */
	public static function regex_token($token)
	{
		$token = str_replace('/', '\/', $token);
		$token = quotemeta($token);
		return str_replace('\\\\', '\\', $token);
	}
}


/*%******************************************************************************************%*/
// REGISTER STREAM WRAPPER

stream_wrapper_register('s3', 'S3StreamWrapper');
