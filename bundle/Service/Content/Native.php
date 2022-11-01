<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service\Content;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\ImageNotFoundException;
use Almaviacx\Bundle\Ibexa\WordPress\Service\ContentInterface;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\ConfigResolverTrait;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\HttpClientTrait;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\IbexaRepositoryTrait;
use Almaviacx\Bundle\Ibexa\WordPress\Service\Traits\LoggerTrait;
use Almaviacx\Bundle\Ibexa\WordPress\ValueObject\WPObject;
use Exception;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentStruct;
use Ibexa\Core\FieldType\TextLine\Value as TextLineValue;
use Ibexa\FieldTypeRichText\FieldType\RichText\Type as RichTextType;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichTextValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class Native implements ContentInterface
{
    use ConfigResolverTrait;
    use IbexaRepositoryTrait;
    use HttpClientTrait;
    use LoggerTrait;

    private RichTextType $richTextType;

    public const OWNER_ID             = 14;
    public const RICHTEXT_EDIT_PREFIX = <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://ibexa.co/namespaces/ezpublish5/xhtml5/edit">
EOD;
    public const RICHTEXT_EDIT_SUFFIX = '</section>';

    public function __construct(RichTextType $richTextType)
    {
        $this->richTextType = $richTextType;
    }

    /**
     * @throws ContentFieldValidationException
     * @throws InvalidArgumentException
     * @throws BadStateException
     * @throws ContentValidationException
     * @throws UnauthorizedException
     * @throws NotFoundException
     * @throws Exception
     */
    public function createContent(
        WPObject $object,
        array $values,
        string $remoteId,
        $parentLocationId = null,
        bool $update = false
    ): ?Content {
        return $this->repository->sudo(
            function () use ($object, $values, $remoteId, $parentLocationId, $update) {
                $contentType = $this->repository->getContentTypeService()
                                    ->loadContentTypeByIdentifier(
                                        $values['content_type'] ?? null
                                    );
                $contentService  = $this->repository->getContentService();
                $locationService = $this->repository->getLocationService();
                $parentLocation  = $locationService->loadLocation(
                    $parentLocationId ?? ($values['parent_location'] ?? null)
                );
                $mappingFields = $values['mapping'] ?? [];

                $fields = [];
                foreach ($contentType->getFieldDefinitions()->toArray() as $field) {
                    if (array_key_exists($field->identifier, $mappingFields)) {
                        $wpObjectAttributeIdentifier = $mappingFields[$field->identifier];
                        if (isset($object->$wpObjectAttributeIdentifier)) {
                            $fields[$field->identifier] = [
                                'value' => $object->$wpObjectAttributeIdentifier,
                                'type' => $field->fieldTypeIdentifier,
                            ];
                        }
                    }
                }
                if ($fields) {
                    try {
                        $content = $contentService->loadContentByRemoteId($remoteId);

                        if (true !== $update) {
                            return $content;
                        }
                        $contentDraft = $contentService->createContentDraft(
                            $content->contentInfo
                        );
                        $contentUpdateStruct                      = $contentService->newContentUpdateStruct();
                        $contentUpdateStruct->initialLanguageCode = $this->getCurrentLang();
                        $contentUpdateStruct->creatorId           = self::OWNER_ID;

                        $this->updateContentStruct($contentUpdateStruct, $fields, $remoteId);

                        $contentDraft = $contentService->updateContent(
                            $contentDraft->versionInfo,
                            $contentUpdateStruct
                        );

                        return $contentService->publishVersion($contentDraft->versionInfo);
                    } catch (NotFoundException $exception) {
                        try {
                            $contentCreateStruct = $contentService->newContentCreateStruct(
                                $contentType,
                                $this->getCurrentLang()
                            );
                            $contentCreateStruct->ownerId  = 14;
                            $contentCreateStruct->remoteId = $remoteId;

                            $this->updateContentStruct($contentCreateStruct, $fields, $remoteId);
                            $locationCreateStruct = $locationService->newLocationCreateStruct($parentLocation->id);

                            $draft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);

                            return $contentService->publishVersion($draft->versionInfo);
                        } catch (Exception $exception) {
                            $this->error(__METHOD__, ['e' => $exception]);
                        }
                    }
                }

                return null;
            }
        );
    }

    private function prepareRichText($inputText): RichTextValue
    {
        if ('' === $inputText) {
            $inputText = '&nbsp;';
        }
        if (strip_tags($inputText) === $inputText) {
            $inputText = "<p>$inputText</p>";
        }
        // Remove Extra code like "[et_pb_section fb_built=&#8221;1&#8243;][ ..."
        $inputText = preg_replace('/\[[^\]]*\]/', '', $inputText);

        if (extension_loaded('tidy')) {
            $tidyConfig = [
                'show-body-only' => true,
                'output-xhtml' => true,
                'wrap' => -1, ];

            $inputText = tidy_parse_string($inputText, $tidyConfig);

            $inputText = str_replace(["\r\n", "\r", "\n"], '', $inputText->root()->value);
        }
        $content = ['xml' => self::RICHTEXT_EDIT_PREFIX.$inputText.self::RICHTEXT_EDIT_SUFFIX];
        $fieldValue = $this->richTextType->fromHash($content);

        return $this->filterValue($fieldValue);
    }

    private function filterValue(RichTextValue $fieldValue): RichTextValue
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->xmlVersion = '1.0';
        $doc->encoding = 'UTF-8';
        $doc->loadXML($fieldValue);
        $section = $doc->firstChild;
        $length = count($section->childNodes);
        foreach ($section->childNodes as $key => $childNode) {
//            if (property_exists($childNode, 'data') && strip_tags($childNode->data) === $childNode->data) {
//                $childNode->nodeValue = '<para>'.$childNode->data.'</para>';
//            }
            if ($key == 0) {
                $childNode->nodeValue = '<para>'.$childNode->data;
            }
            if ($length-1 == $key) {
                $childNode->nodeValue = $childNode->data.'</para>';
            }
        }
        $data = $doc->saveXML();
        $data = str_replace('&lt;', '<', $data);
        $data = str_replace('&gt;', '>', $data);

        return new RichTextValue($data);
    }

    private function prepareImage(?string $url, ?string $remoteId): string
    {
        if ('' === trim($url ?? '')) {
            return '';
        }

        try {
            $response = $this->client->request(
                Request::METHOD_GET,
                $url
            );
            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException('Status code is not 200: '.$response->getStatusCode());
            }
            $baseName      = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_BASENAME);
            $exportPath = $this->getLocalImageStorageDir();
            if (!file_exists($exportPath)) {
                mkdir($exportPath, 0777, true);
            }
            $temporaryPath = $exportPath.'/'.$this->getImageName($remoteId, $baseName);
            $fileHandler   = fopen($temporaryPath, 'w');
            foreach ($this->client->stream($response) as $chunk) {
                fwrite($fileHandler, $chunk->getContent());
            }
            fclose($fileHandler);

            return $temporaryPath;
        } catch (\Exception|ExceptionInterface $exception) {
            throw new ImageNotFoundException($url, [], $exception);
        }
    }

    private function updateContentStruct(ContentStruct $contentStruct, $fieldSettings, string $remoteId)
    {
        foreach ($fieldSettings as $identifier => $fieldInfo) {
            $fieldValue = $fieldInfo['value'];
            if ('ezrichtext' === $fieldInfo['type']) {
                $fieldValue = $this->prepareRichText($fieldValue);
            }
            if ('ezimage' === $fieldInfo['type']) {
                $fieldValue = $this->prepareImage($fieldValue, $remoteId);
            }
            if ('ezstring' === $fieldInfo['type']) {
                $fieldValue = new TextLineValue($fieldValue);
            }
            $contentStruct->setField($identifier, $fieldValue);
        }
    }
}
