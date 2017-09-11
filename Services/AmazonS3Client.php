<?php

namespace Angle\Common\S3Bundle\Services;

use Aws\S3\S3Client;

class AmazonS3Client
{
    protected $service;
    protected $bucket;
    protected $options;
    protected $bucketExists;
    protected $metadata = array();
    protected $detectContentType = true;

    public function __construct($amazon_s3_key, $amazon_s3_secret, $amazon_s3_bucket, $amazon_s3_region, array $options = array())
    {
        // Create an Amazon S3 client object
        $this->service = new S3Client(array(
            'region' => $amazon_s3_region,
            'version' => 'latest',
            'credentials' => array(
                'key' => $amazon_s3_key,
                'secret'  => $amazon_s3_secret,
            )
        ));

        $this->bucket = $amazon_s3_bucket;

        $this->options = array_replace(
            array(
                'create' => false,
                'directory' => '',
                'acl' => 'private',
            ),
            $options
        );
    }

    /**
     * Gets the publicly accessible URL of an Amazon S3 object
     *
     * @param string $key     Object key
     * @param array  $options Associative array of options used to buld the URL
     *                       - expires: The time at which the URL should expire
     *                           represented as a UNIX timestamp
     *                       - Any options available in the Amazon S3 GetObject
     *                           operation may be specified.
     * @return string
     */
    public function getUrl($key, array $options = array())
    {
        return $this->service->getObjectUrl(
            $this->bucket,
            $this->computePath($key),
            isset($options['expires']) ? $options['expires'] : null,
            $options
        );
    }

    public function setMetadata($key, $metadata)
    {
        // BC with AmazonS3 adapter
        if (isset($metadata['contentType'])) {
            $metadata['ContentType'] = $metadata['contentType'];
            unset($metadata['contentType']);
        }
        $this->metadata[$key] = $metadata;
    }

    public function getMetadata($key)
    {
        return isset($this->metadata[$key]) ? $this->metadata[$key] : array();
    }

    public function read($key)
    {
        $this->ensureBucketExists();
        $options = $this->getOptions($key);
        try {
            return (string) $this->service->getObject($options)->get('Body');
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rename($sourceKey, $targetKey)
    {
        $this->ensureBucketExists();
        $options = $this->getOptions(
            $targetKey,
            array(
                'CopySource' => $this->bucket.'/'.$this->computePath($sourceKey),
            )
        );
        try {
            $this->service->copyObject($options);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function write($key, $content, $contentType=null)
    {
        $this->ensureBucketExists();
        $options = $this->getOptions($key, array('Body' => $content));
        if ($contentType) $options['ContentType'] = $contentType; // Hotfix: 2015-Apr-15
        /**
         * If the ContentType was not already set in the metadata, then we autodetect
         * it to prevent everything being served up as binary/octet-stream.
         */
        if (!isset($options['ContentType']) && $this->detectContentType) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($content);
            $options['ContentType'] = $mimeType;
        }
        try {
            $this->service->putObject($options);
            return strlen($content);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function exists($key)
    {
        return $this->service->doesObjectExist($this->bucket, $this->computePath($key));
    }

    public function mtime($key)
    {
        try {
            $result = $this->service->headObject($this->getOptions($key));
            return strtotime($result['LastModified']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function keys()
    {
        return $this->listKeys();
    }

    public function listKeys($prefix = '')
    {
        $options = array('Bucket' => $this->bucket);
        if ((string) $prefix != '') {
            $options['Prefix'] = $this->computePath($prefix);
        } elseif (!empty($this->options['directory'])) {
            $options['Prefix'] = $this->options['directory'];
        }
        $keys = array();
        $iter = $this->service->getIterator('ListObjects', $options);
        foreach ($iter as $file) {
            $keys[] = $file['Key'];
        }
        return $keys;
    }

    public function delete($key)
    {
        try {
            $this->service->deleteObject($this->getOptions($key));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isDirectory($key)
    {
        $result = $this->service->listObjects(array(
            'Bucket'  => $this->bucket,
            'Prefix'  => rtrim($this->computePath($key), '/') . '/',
            'MaxKeys' => 1
        ));
        return count($result['Contents']) > 0;
    }
    /**
     * Ensures the specified bucket exists. If the bucket does not exists
     * and the create option is set to true, it will try to create the
     * bucket. The bucket is created using the same region as the supplied
     * client object.
     *
     * @throws \RuntimeException if the bucket does not exists or could not be
     *                          created
     */
    protected function ensureBucketExists()
    {
        if ($this->bucketExists) {
            return true;
        }
        if ($this->bucketExists = $this->service->doesBucketExist($this->bucket)) {
            return true;
        }
        if (!$this->options['create']) {
            throw new \RuntimeException(sprintf(
                'The configured bucket "%s" does not exist.',
                $this->bucket
            ));
        }
        $options = array('Bucket' => $this->bucket);
        if ($this->service->getRegion() != 'us-east-1') {
            $options['LocationConstraint'] = $this->service->getRegion();
        }
        $this->service->createBucket($options);
        $this->bucketExists = true;
        return true;
    }
    protected function getOptions($key, array $options = array())
    {
        $options['ACL'] = $this->options['acl'];
        $options['Bucket'] = $this->bucket;
        $options['Key'] = $this->computePath($key);
        /**
         * Merge global options for adapter, which are set in the constructor, with metadata.
         * Metadata will override global options.
         */
        $options = array_merge($this->options, $options, $this->getMetadata($key));
        return $options;
    }
    protected function computePath($key)
    {
        if (empty($this->options['directory'])) {
            return $key;
        }
        return sprintf('%s/%s', $this->options['directory'], $key);
    }

}