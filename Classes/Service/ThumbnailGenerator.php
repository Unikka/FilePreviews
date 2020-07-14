<?php

namespace Unikka\FilePreviews\Service;

/*
 * This file is part of the Unikka.FilePreviews package.
 *
 * (c) unikka
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\Thumbnail;
use Neos\Media\Domain\Repository\ThumbnailRepository;
use Neos\Media\Exception;
use Neos\Utility\Arrays;
use Psr\Log\LoggerInterface;
use Flowpack\JobQueue\Common\Annotations as Job;

/**
 * File Previews Client Service
 */
class ThumbnailGenerator
{
    const API_STATUS_PENDING = 'pending';

    /**
     * @var ResourceManager
     * @Flow\Inject
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @Flow\InjectConfiguration(package="Unikka.FilePreviews")
     * @var array
     */
    protected $settings;

    /**
     * @Flow\Inject
     * @var ThumbnailRepository
     */
    protected $thumbnailRepository;

    /**
     * @param Thumbnail $thumbnail
     * @return void
     * @throws Exception\NoThumbnailAvailableException
     * @throws \Exception
     */
    public function submitThumbnailToFilePreviewApi($thumbnail)
    {
        $originalResource = $thumbnail->getOriginalAsset()->getResource();
        $uri =$this->resourceManager->getPublicPersistentResourceUri($originalResource);
        $width = $thumbnail->getConfigurationValue('width') ?: $thumbnail->getConfigurationValue('maximumWidth');
        $height = $thumbnail->getConfigurationValue('height') ?: $thumbnail->getConfigurationValue('maximumHeight');

        $previewApiClient = $this->getApiClient();
        $response = $previewApiClient->generate($uri, Arrays::arrayMergeRecursiveOverrule([
            'sizes' => [$width, $height],
            'format' => 'jpg',
            'data' => [
                'original' => $thumbnail->getOriginalAsset()->getResource()->getSha1()
            ]
        ], $this->settings['defaultOptions']));

        if ($response->status === self::API_STATUS_PENDING) {
            $responseIdentifier = $response->id;
            $this->fetchThumbnailFromFilePreviewApi($responseIdentifier, $thumbnail);
        }
    }

    /**
     * @Job\Defer(queueName="filepreview-queue")
     * @param string $previewIdentifier
     * @param Thumbnail $thumbnail
     * @return void
     * @throws Exception
     * @throws \Neos\Flow\ResourceManagement\Exception
     */
    public function fetchThumbnailFromFilePreviewApi($previewIdentifier, Thumbnail $thumbnail)
    {
        $success = false;
        $elapsedTime = 0;
        $maximumWaitingTime = (integer)$this->settings['maximumWaitingTime'];
        $retryInterval = (integer)$this->settings['retryInterval'];
        while ($success === false) {
            if ($elapsedTime >= $maximumWaitingTime) {
                break;
            }
            $previewApiClient = $this->getApiClient();
            $response = $previewApiClient->retrieve($previewIdentifier);

            $success = $response->status === 'success';
            sleep($retryInterval);
            $elapsedTime = $elapsedTime + $retryInterval;
        }

        if ($success === false || !isset($response->thumbnails[0])) {
            throw new Exception('Unable to process the thumbnail is less than 20 seconds, sorry', 1447891433);
        }

        $url = $response->thumbnails[0]->url;
        $size = $response->thumbnails[0]->size;

        $resource = $this->resourceManager->importResource($url);
        $thumbnail->setStaticResource('');
        $thumbnail->setResource($resource);
        $thumbnail->setWidth($size->width);
        $thumbnail->setHeight($size->height);
        $this->thumbnailRepository->update($thumbnail);
    }

    /**
     * @return ApiClient
     * @throws \Exception
     */
    protected function getApiClient()
    {
        return new ApiClient([
            'api_key' => $this->settings['apiKey'],
            'api_secret' => $this->settings['apiSecret']
        ]);
    }
}
