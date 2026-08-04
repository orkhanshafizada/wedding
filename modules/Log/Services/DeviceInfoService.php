<?php
namespace Modules\Log\Services;

final class DeviceInfoService
{
    public function parse(?string $userAgent): array
    {
        $ua = (string) $userAgent;

        $deviceType = $this->detectDeviceType($ua);
        [$browser, $browserVersion] = $this->detectBrowser($ua);
        [$os, $osVersion] = $this->detectOs($ua);

        [$brand, $model] = $this->detectBrandModel($ua);

        return [
            'device_type' => $deviceType,
            'device_brand' => $brand,
            'device_model' => $model,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'os' => $os,
            'os_version' => $osVersion,
        ];
    }

    private function detectDeviceType(string $ua): string
    {
        $l = mb_strtolower($ua);

        if (str_contains($l, 'ipad') || str_contains($l, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($l, 'mobi') || str_contains($l, 'iphone') || str_contains($l, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectBrowser(string $ua): array
    {
        $map = [
            'Edg/' => 'Edge',
            'OPR/' => 'Opera',
            'Chrome/' => 'Chrome',
            'Firefox/' => 'Firefox',
            'Safari/' => 'Safari',
        ];

        foreach ($map as $needle => $name) {
            $pos = strpos($ua, $needle);
            if ($pos !== false) {
                $ver = substr($ua, $pos + strlen($needle));
                $ver = strtok($ver, ' )_;');
                if ($name === 'Safari' && strpos($ua, 'Chrome/') !== false) {
                    continue;
                }
                return [$name, $ver ?: null];
            }
        }

        return [null, null];
    }

    private function detectOs(string $ua): array
    {
        $l = mb_strtolower($ua);

        if (str_contains($l, 'windows nt')) {
            $ver = $this->extractAfter($ua, 'Windows NT ');
            return ['Windows', $ver];
        }

        if (str_contains($l, 'android')) {
            $ver = $this->extractAfter($ua, 'Android ');
            return ['Android', $ver];
        }

        if (str_contains($l, 'iphone') || str_contains($l, 'ipad') || str_contains($l, 'ios')) {
            $ver = $this->extractIosVersion($ua);
            return ['iOS', $ver];
        }

        if (str_contains($l, 'mac os x')) {
            $ver = $this->extractAfter($ua, 'Mac OS X ');
            $ver = $ver ? str_replace('_', '.', $ver) : null;
            return ['macOS', $ver];
        }

        if (str_contains($l, 'linux')) {
            return ['Linux', null];
        }

        return [null, null];
    }

    private function detectBrandModel(string $ua): array
    {
        $l = mb_strtolower($ua);

        if (str_contains($l, 'iphone')) {
            return ['Apple', 'iPhone'];
        }

        if (str_contains($l, 'ipad')) {
            return ['Apple', 'iPad'];
        }

        if (str_contains($l, 'android')) {
            $model = null;
            if (preg_match('/Android\s[\d\.]+;\s([^;]+)\sBuild/i', $ua, $m)) {
                $model = trim((string) $m[1]);
            }

            $brand = null;
            if ($model) {
                $brand = $this->guessAndroidBrand($model);
            }

            return [$brand, $model];
        }

        return [null, null];
    }

    private function guessAndroidBrand(string $model): ?string
    {
        $m = mb_strtolower($model);

        $brands = [
            'samsung' => ['sm-', 'samsung'],
            'xiaomi' => ['mi ', 'm200', 'redmi', 'poco'],
            'huawei' => ['huawei', 'honor', 'lya-', 'ane-'],
            'oppo' => ['cph', 'oppo'],
            'vivo' => ['vivo'],
            'oneplus' => ['oneplus', 'gm19', 'kb200'],
            'google' => ['pixel', 'google'],
            'realme' => ['rmx', 'realme'],
        ];

        foreach ($brands as $brand => $needles) {
            foreach ($needles as $n) {
                if (str_contains($m, $n)) {
                    return ucfirst($brand);
                }
            }
        }

        return null;
    }

    private function extractAfter(string $ua, string $needle): ?string
    {
        $pos = strpos($ua, $needle);
        if ($pos === false) {
            return null;
        }

        $part = substr($ua, $pos + strlen($needle));
        $part = strtok($part, '; )');
        return $part ? trim((string) $part) : null;
    }

    private function extractIosVersion(string $ua): ?string
    {
        if (preg_match('/OS\s(\d+[_\d]*)\slike\sMac\sOS\sX/i', $ua, $m)) {
            return str_replace('_', '.', (string) $m[1]);
        }

        return null;
    }
}
