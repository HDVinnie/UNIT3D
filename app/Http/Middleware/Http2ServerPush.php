<?php

declare(strict_types=1);
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class Http2ServerPush
{
    /**
     * The DomCrawler instance.
     *
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected Crawler $crawler;

    /**
     * @var string[]
     */
    private const LINK_TYPE_MAP = [
        '.CSS'  => 'style',
        '.JS'   => 'script',
    ];

    /**
     * Handle an incoming request.
     *
     * @param null $limit
     * @param null $sizeLimit
     * @param null $excludeKeywords
     */
    public function handle(Request $request, Closure $next, $limit = null, $sizeLimit = null, $excludeKeywords = null): mixed
    {
        $response = $next($request);

        if ($response->isRedirection() || ! $response instanceof Response || $request->isJson()) {
            return $response;
        }

        $this->generateAndAttachLinkHeaders($response, $limit, $sizeLimit, $excludeKeywords);

        return $response;
    }

    public function getConfig($key, $default = false)
    {
        if (! \function_exists('config')) { // for tests..
            return $default;
        }

        return \config('http2serverpush.'.$key, $default);
    }

    /**
     * @param null $limit
     * @param null $sizeLimit
     * @param null $excludeKeywords
     *
     * @return $this
     */
    protected function generateAndAttachLinkHeaders(Response $response, $limit = null, $sizeLimit = null, $excludeKeywords = null): self
    {
        $excludeKeywords ?? $this->getConfig('exclude_keywords', []);
        $headers = $this->fetchLinkableNodes($response)
            ->flatten(1)
            ->map(fn ($url) => $this->buildLinkHeaderString($url))
            ->unique()
            ->filter(function ($value, $key) use ($excludeKeywords) {
                if (! $value) {
                    return false;
                }
                $excludeKeywords = \collect($excludeKeywords)->map(fn ($keyword) => \preg_quote($keyword));
                if ($excludeKeywords->count() <= 0) {
                    return true;
                }

                return ! \preg_match('%('.$excludeKeywords->implode('|').')%i', $value);
            })
            ->take($limit);

        $sizeLimit ??= \max(1, (int) $this->getConfig('size_limit', 32 * 1_024));
        $headersText = \trim($headers->implode(','));
        while (\strlen($headersText) > $sizeLimit) {
            $headers->pop();
            $headersText = \trim($headers->implode(','));
        }

        if (! empty($headersText)) {
            $this->addLinkHeader($response, $headersText);
        }

        return $this;
    }

    /**
     * Get the DomCrawler instance.
     */
    protected function getCrawler(Response $response): Crawler
    {
        if ($this->crawler) {
            return $this->crawler;
        }

        return $this->crawler = new Crawler($response->getContent());
    }

    /**
     * Get all nodes we are interested in pushing.
     *
     * @param \Illuminate\Http\Response $response
     *
     * @return \Illuminate\Support\Collection
     */
    protected function fetchLinkableNodes(Response $response): \Illuminate\Support\Collection
    {
        $crawler = $this->getCrawler($response);

        return \collect($crawler->filter('link:not([rel*="icon"]), script[src], img[src], object[data]')->extract(['src', 'href', 'data']));
    }

    /**
     * Build out header string based on asset extension.
     *
     * @param string $url
     *
     * @return string|null
     */
    private function buildLinkHeaderString(string $url): ?string
    {
        $type = \collect(self::LINK_TYPE_MAP)->first(fn ($type, $extension) => Str::contains(\strtoupper($url), $extension));
        if (! \preg_match('#^https?://#i', $url)) {
            $basePath = $this->getConfig('base_path', '/');
            $url = $basePath.\ltrim($url, $basePath);
        }

        return \is_null($type) ? null : \sprintf('<%s>; rel=preload; as=%s', $url, $type);
    }

    /**
     * Add Link Header.
     *
     * @param \Illuminate\Http\Response $response
     * @param                           $link
     */
    private function addLinkHeader(Response $response, $link): void
    {
        if ($response->headers->get('Link')) {
            $link = $response->headers->get('Link').','.$link;
        }

        $response->header('Link', $link);
    }
}
