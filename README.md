<p align="center">
  <img src="https://cdn.jsdelivr.net/gh/unikka/unikka.de/src/assets/unikka_with_background.svg" width="300" />
</p>

[![Packagist](https://img.shields.io/packagist/l/unikka/filepreviews.svg?style=flat-square)](https://packagist.org/packages/unikka/filepreviews)
![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability-percentage/Unikka/FilePreviews)
[![Packagist](https://img.shields.io/packagist/v/unikka/filepreviews.svg?style=flat-square)](https://packagist.org/packages/unikka/filepreviews)
[![semantic-release](https://img.shields.io/badge/%20%20%F0%9F%93%A6%F0%9F%9A%80-semantic--release-e10079.svg)](https://github.com/semantic-release/semantic-release)

# Neos CMS filepreviews.io integration

This package generate thumbnail and extract metadata from different type of document
based on the API of [filepreviews.io].

How it work ?
-------------

This Generator call the FilePreviews.io API to generate Thumbnail for many different file formats. Check [filepreviews.io]
website for more informations.

Getting started
-------------

To use this package you need an account at [filepreview.io]. Create an account if you do not have one yet. Otherwise you can not use this package.

If you already have an account you only have to look up your `API Key` and the corresponding `Secret`.

Install the package via composer:
```
composer require unicka/filepreviews
```

Configure the API credentials:
```yaml
Unikka:
  FilePreviews:
    apiKey: 'your-key'
    apiSecret: 'your-secret'
```

To get the preview images via filepreview.io we use a queue. This means that when you upload a new file to Neos we first create a standard preview image and then request it from the service.
When the preview image is available for your website we download it and update the previous preview image.

For the queue to work you need to set it up initially. This is done with the command:
```bash
./flow queue:setup filepreview-queue
```

The command before initializes everything and we get an additional database table `flowpack_jobqueue_messages_filepreview-queue` where we list all our thumbnail requests to [filepreviews.io].
Now we just have to make sure that a worker is running and processing all our jobs. If an error occurs or a file is not yet ready, the file is put back and queried again.
```bash
./flow flowpack.jobqueue.common:job:work filepreview-queue
```

Configuration
-------------

Like any other Thumbnail Generator, you can change default settings. First step, like mentioned before in the getting stated section, you need to configure your API keys.

```yaml
Unikka:
  FilePreviews:
    apiKey: 'key'
    apiSecret: 'secret'
    defaultOptions:
      format: 'jpg'
```

- ```apiKey```: check [filepreviews.io]
- ```apiSecret```: check [filepreviews.io]
- ```defaultOptions```: check the [API endpoint] documentation

We use our own thumbnail generator and accordingly different options can be assigned. You can use the FilePreviewThumbnailGenerator with features like the supportedExtensions selectiv for only some specific file formats. For example, it is not very useful to use the generator for images, because Neos can process them itself. [Supported Formats] from filepreviews.io

```yaml
Neos:
  Media:
    thumbnailGenerators:
      'Unikka\FilePreviews\Domain\Model\ThumbnailGenerator\FilePreviewsThumbnailGenerator':
        maximumFileSize: 2000000
        supportedExtensions: [ 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlxs', 'odt', 'ott', 'odp', 'txt', 'rtf', 'eps', 'psd', 'ai' ]
```

- ```supportedExtensions```: check the official documentation of FilePreviews [Supported Formats] and enjoy.
- ```maximumFileSize```: Default is 2000000

We use the packages [Flowpack.JobQueue.Common](https://github.com/Flowpack/jobqueue-common "Common queue package") and [Flowpack.JobQueue.Doctrine](https://github.com/Flowpack/jobqueue-doctrine "A job queue backend for the Flowpack.JobQueue.Common package")
and these packages come with a lot of own configurations. See the docs of the packages. We use the doctrine backend to have error handling out of the box. But if you want to use redis for instance you can configure that and just replace the className.

```yaml
Flowpack:
  JobQueue:
    Common:
      queues:
        'filepreview-queue':
          className: 'Flowpack\JobQueue\Doctrine\Queue\DoctrineQueue'
          maximumNumberOfReleases: 3
          executeIsolated: true
          options:
            defaultTimeout: 30
          releaseOptions:
            priority: 512
            delay: 15
```



## Contribution

We'd love you to contribute to neos-slick. We try to make it as easy as possible.
We are using semantic versioning to have more time to concentrate on important stuff
instead of struggling in the dependency or release hell.

Therefore the first rule is to follow the [eslint commit message guideline](https://github.com/conventional-changelog-archived-repos/conventional-changelog-eslint/blob/master/convention.md).
It is really easy if you always commit via `yarn commit`. Commitizen will guide you.

All PRs will be merged into the master branch. Travis and semantic release will check the commit messages and start
building a new release when the analysis of the latest commits will trigger that.

If you have questions just ping us on Twitter or Github.

## About

The package is based on the `Ttree\FilePreviews` package. We thank the [ttree team](https://ttree.ch) for
all the efforts and initial development.

## License
The GNU GENERAL PUBLIC LICENSE (Version 3). Please see [License File](LICENSE) for more information.


[filepreviews.io]: http://filepreviews.io/
[Supported Formats]: https://filepreviews.io/docs/features/
[API endpoint]: https://filepreviews.io/docs/endpoints/
