<?php

namespace Almaviacx\Bundle\Ibexa\WordPress\Service\Traits;

use Almaviacx\Bundle\Ibexa\WordPress\Exceptions\Exception;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

trait ConfigResolverTrait
{
    protected ConfigResolverInterface $configResolver;

    /**
     * @required
     */
    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @throws Exception
     */
    protected function getBaseURl(string $namespace): string
    {
        $baseUrl = (string) $this->configResolver->getParameter('url', $namespace);
        $scheme  = parse_url($baseUrl, PHP_URL_SCHEME);
        $host    = parse_url($baseUrl, PHP_URL_HOST);
        if (empty($scheme) || empty($host)) {
            throw new Exception($baseUrl ?? 'No base URL');
        }

        return $baseUrl;
    }

    /**
     * @throws Exception
     */
    public function getRequestedUrl(string $serviceURL, string $prefix, string $namespace): string
    {
        $baseUrl = $this->getBaseURl($namespace);

        return trim($baseUrl, '/').'/'.$prefix.'/'.trim($serviceURL, '/');
    }

    private function getPerPage(string $root): int
    {
        $values = $this->configResolver->getParameter($root, self::NAMESPACE);

        return (int) ($values['per_page'] ?? null);
    }

    public function getRootLocationId(): int
    {
        return (int) $this->configResolver->getParameter('content.tree_root.location_id');
    }

    private function getCurrentLang()
    {
        $langs = $this->configResolver->getParameter('languages');

        return $langs[0] ?? 'eng-GB';
    }
}
