# JplararS3Bundle
A simple Symfony2 bundle for the API for AWS S3.

## Setup

### Step 1: Download JplararS3Bundle using composer

Add S3 Bundle in your composer.json:

```js
{
    "require": {
        "jplarar/s3-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update "jplarar/s3-bundle"
```


### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Jplarar\S3Bundle\JplararS3Bundle()
    );
}
```

### Step 3: Add configuration

``` yml
# app/config/config.yml
jplarar_s3:
        amazon_s3:
            amazon_s3_key:    %amazon_s3_key%
            amazon_s3_secret: %amazon_s3_secret%
            amazon_s3_bucket: %amazon_s3_bucket%
            amazon_s3_region: %amazon_s3_region%
```

## Usage

**Using service**

``` php
<?php
        $s3Client = $this->get('amazon_s3_client');
?>
```

##Example

###Upload new file to S3
``` php
<?php 
    $s3Client->write($key, $content, $mimeType);
?>
```
