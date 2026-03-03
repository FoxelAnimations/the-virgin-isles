<?php

namespace App\Services;

use App\Models\Beacon;
use App\Models\BeaconScan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BeaconScanService
{
    /**
     * Sensitive query parameters that must never be stored.
     */
    private const SENSITIVE_PARAMS = [
        'token', 'auth', 'session', 'password', 'secret', 'key', 'api_key', 'apikey',
        'access_token', 'refresh_token', 'authorization', 'credential', 'passwd',
    ];

    /**
     * Hash an IP address using SHA-256 with the configured secret salt.
     */
    public function hashIp(string $ip): string
    {
        $salt = config('beacon.ip_salt');
        return hash('sha256', $ip . $salt);
    }

    /**
     * Sanitize a URL by stripping sensitive query parameters.
     */
    public function sanitizeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $parsed = parse_url($url);
        if ($parsed === false || !isset($parsed['query'])) {
            return $url;
        }

        parse_str($parsed['query'], $params);
        foreach (self::SENSITIVE_PARAMS as $sensitive) {
            unset($params[$sensitive]);
            unset($params[strtoupper($sensitive)]);
        }

        // Rebuild URL preserving its original structure (absolute or relative)
        $clean = '';
        if (isset($parsed['scheme'])) {
            $clean .= $parsed['scheme'] . '://';
        }
        if (isset($parsed['host'])) {
            $clean .= $parsed['host'];
            if (isset($parsed['port'])) {
                $clean .= ':' . $parsed['port'];
            }
        }
        $clean .= $parsed['path'] ?? '/';
        if (!empty($params)) {
            $clean .= '?' . http_build_query($params);
        }
        if (isset($parsed['fragment'])) {
            $clean .= '#' . $parsed['fragment'];
        }

        return $clean;
    }

    /**
     * Validate that a redirect URL is safe (internal path or matching APP_URL domain).
     */
    public function isRedirectSafe(string $url): bool
    {
        // Relative paths are always safe
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }

        $parsed = parse_url($url);
        if (!isset($parsed['host'])) {
            return true;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        return $parsed['host'] === $appHost;
    }

    /**
     * Resolve the final redirect URL for a beacon, falling back to homepage.
     */
    public function resolveRedirectUrl(Beacon $beacon): string
    {
        $url = $beacon->redirect_url ?: '/';
        return $this->isRedirectSafe($url) ? $url : '/';
    }

    /**
     * Resolve the redirect/response behavior for an out-of-action beacon.
     * Returns ['type' => 'redirect'|'page'|'block', 'url' => ..., 'message' => ...]
     */
    public function resolveOutOfActionBehavior(Beacon $beacon): array
    {
        return match ($beacon->out_of_action_mode) {
            'redirect' => [
                'type' => 'redirect',
                'url' => $this->resolveRedirectUrl($beacon),
            ],
            'redirectCustom' => [
                'type' => 'redirect',
                'url' => ($beacon->out_of_action_redirect_url && $this->isRedirectSafe($beacon->out_of_action_redirect_url))
                    ? $beacon->out_of_action_redirect_url
                    : '/',
            ],
            'showPage' => [
                'type' => 'page',
                'message' => $beacon->out_of_action_message,
                'beacon' => $beacon,
            ],
            'block' => [
                'type' => 'block',
            ],
            default => [
                'type' => 'page',
                'message' => null,
                'beacon' => $beacon,
            ],
        };
    }

    /**
     * Log a scan. Always logs — every scan counts.
     */
    public function logScan(Request $request, string $guid, ?Beacon $beacon, string $redirectUrlUsed, bool $rateLimited = false): BeaconScan
    {
        return BeaconScan::create([
            'scanned_at' => now(),
            'guid' => $guid,
            'beacon_id' => $beacon?->id,
            'is_known' => $beacon !== null,
            'hashed_ip' => $this->hashIp($request->ip()),
            'user_agent' => Str::limit($request->userAgent() ?? '', 1000),
            'referrer' => $this->sanitizeUrl($request->header('referer')),
            'requested_url' => $this->sanitizeUrl($request->fullUrl()),
            'redirect_url_used' => $redirectUrlUsed,
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_term' => $request->query('utm_term'),
            'utm_content' => $request->query('utm_content'),
            'rate_limited' => $rateLimited,
            'meta_json' => $this->buildMetaJson($request),
        ]);
    }

    /**
     * Build the meta_json field with sanitized query snapshot.
     */
    private function buildMetaJson(Request $request): ?array
    {
        $query = $request->query();
        if (empty($query)) {
            return null;
        }

        // Remove sensitive params and UTM (already stored separately)
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        foreach (array_merge(self::SENSITIVE_PARAMS, $utmKeys) as $key) {
            unset($query[$key]);
            unset($query[strtoupper($key)]);
        }

        return !empty($query) ? ['query' => $query] : null;
    }

    /**
     * Delete scans older than the configured retention period.
     */
    public function cleanupOldScans(): int
    {
        $days = config('beacon.log_retention_days', 365);
        return BeaconScan::where('scanned_at', '<', now()->subDays($days))->delete();
    }
}
