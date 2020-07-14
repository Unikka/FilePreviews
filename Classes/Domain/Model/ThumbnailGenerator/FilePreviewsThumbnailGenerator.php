<?php

namespace Unikka\FilePreviews\Domain\Model\ThumbnailGenerator;

/*
 * This file is part of the Unikka.FilePreviews package.
 *
 * (c) unikka and ttree ltd
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Media\Domain\Service\FileTypeIconService;
use Psr\Log\LoggerInterface;
use Unikka\FilePreviews\Service\FilePreviewsService;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Media\Domain\Model\Thumbnail;
use Neos\Media\Domain\Model\ThumbnailGenerator\AbstractThumbnailGenerator;
use Neos\Media\Exception;
use Unikka\FilePreviews\Service\ThumbnailGenerator;

/**
 * A system-generated preview version of a Document (DOCX, AI and EPS)
 */
class FilePreviewsThumbnailGenerator extends AbstractThumbnailGenerator
{

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @Flow\Inject
     * @var ThumbnailGenerator
     */
    protected $thumbnailGeneratorService;

    /**
     * @param Thumbnail $thumbnail
     * @return boolean
     */
    public function canRefresh(Thumbnail $thumbnail)
    {
        return $this->isExtensionSupported($thumbnail);
    }

    /**
     * @param Thumbnail $thumbnail
     * @return void
     * @throws Exception\NoThumbnailAvailableException
     */
    public function refresh(Thumbnail $thumbnail)
    {
        $originalResource = $thumbnail->getOriginalAsset()->getResource();
        $filename = $originalResource->getFilename();
        $sha1 = $originalResource->getSha1();
        try {
            // set temporary thumbnail
            $width = $thumbnail->getConfigurationValue('width') ?: $thumbnail->getConfigurationValue('maximumWidth');
            $height = $thumbnail->getConfigurationValue('height') ?: $thumbnail->getConfigurationValue('maximumHeight');

            $icon = FileTypeIconService::getIcon($filename);
            $thumbnail->setStaticResource($icon['src']);
            $thumbnail->setWidth($width);
            $thumbnail->setHeight($height);

            // start async request for file-preview
            $maximumFileSize = (integer)$this->getOption('maximumFileSize');
            if ($originalResource->getFileSize() <= $maximumFileSize) {
                $this->thumbnailGeneratorService->submitThumbnailToFilePreviewApi($thumbnail);
            } else {
                $message = sprintf(
                    'The file size limit has been exceeded for the given document (filename: %s, SHA1: %s)',
                    $filename,
                    $sha1
                );
                $this->logger->error($message);
            }
        } catch (\Exception $exception) {
            $message = sprintf(
                'FilePreview.io was unable to generate thumbnail for the given document (filename: %s, SHA1: %s)',
                $filename,
                $sha1
            );
            throw new Exception\NoThumbnailAvailableException($message, 1447883095, $exception);
        }
    }
}
