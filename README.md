# Neos CMS filepreviews.io integration

This package generate thumbnail and extract metadata from different type of document
based on the API of [filepreviews.io].

How it work ?
-------------

This Generator call the FilePreviews.io API to generate Thumbnail for many different file formats. Check [filepreviews.io]
website for more informations.

Configuration
-------------

Like any other Thumbnail Generator, you can change default settings. First step, you need to configure your API keys.

```yaml
Unikka:
  FilePreviews:
    apiKey: 'key'
    apiSecret: 'secret'
    defaultOptions:
      format: 'jpg'
```

```yaml
Neos:
  Media:
    thumbnailGenerators:
      'Unikka\FilePreviews\Domain\Model\ThumbnailGenerator\FilePreviewsThumbnailGenerator':
        maximumFileSize: 2000000
        supportedExtensions: [ 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlxs', 'odt', 'ott', 'odp', 'txt', 'rtf', 'eps', 'psd', 'ai' ]
```

- ```supportedExtensions```: check the official documentation of FilePreviews [Supported Formats] and enjoy.
- ```defaultOptions```: check the [API endpoint] documentation.


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
