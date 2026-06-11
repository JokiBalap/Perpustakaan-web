<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\Blade::directive('vite', function ($expression) {
            return "<?php echo \App\Providers\AppServiceProvider::viteAssets($expression); ?>";
        });
    }

    /**
     * Helper to render Vite assets in Laravel 8
     * Uses the current request base URL so it works with ngrok, localhost, etc.
     *
     * @param array|string $assets
     * @return string
     */
    public static function viteAssets($assets)
    {
        if (is_string($assets)) {
            $cleaned = str_replace(['[', ']', "'", '"', ' '], '', $assets);
            $assets = explode(',', $cleaned);
        }

        // Get base URL from the current request (works with ngrok, localhost, any domain)
        try {
            $request = app('request');
            $baseUrl = $request->getSchemeAndHttpHost();
        } catch (\Exception $e) {
            $baseUrl = config('app.url', 'http://localhost:8000');
        }

        $html = '';
        $manifestPath = public_path('build/manifest.json');

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            foreach ($assets as $asset) {
                $asset = trim($asset);
                if (isset($manifest[$asset])) {
                    $file = $manifest[$asset]['file'];
                    $fileUrl = $baseUrl . '/build/' . $file;

                    if (substr($file, -4) === '.css') {
                        $html .= "<link rel=\"stylesheet\" href=\"{$fileUrl}\">\n";
                    } elseif (substr($file, -3) === '.js') {
                        $html .= "<script type=\"module\" src=\"{$fileUrl}\"></script>\n";

                        // Load accompanying CSS files if any
                        if (isset($manifest[$asset]['css'])) {
                            foreach ($manifest[$asset]['css'] as $cssFile) {
                                $cssUrl = $baseUrl . '/build/' . $cssFile;
                                $html .= "<link rel=\"stylesheet\" href=\"{$cssUrl}\">\n";
                            }
                        }
                    }
                }
            }
        } else {
            // Fallback: no manifest (dev mode without Vite running)
            foreach ($assets as $asset) {
                $asset = trim($asset);
                if (substr($asset, -4) === '.css') {
                    $html .= "<link rel=\"stylesheet\" href=\"{$baseUrl}/{$asset}\">\n";
                } else {
                    $html .= "<script type=\"module\" src=\"{$baseUrl}/{$asset}\"></script>\n";
                }
            }
        }

        return $html;
    }
}
