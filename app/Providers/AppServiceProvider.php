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

        $html = '';
        $manifestPath = public_path('build/manifest.json');

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            foreach ($assets as $asset) {
                if (isset($manifest[$asset])) {
                    $fileUrl = asset('build/' . $manifest[$asset]['file']);
                    if (substr($fileUrl, -4) === '.css') {
                        $html .= "<link rel=\"stylesheet\" href=\"{$fileUrl}\">\n";
                    } elseif (substr($fileUrl, -3) === '.js') {
                        $html .= "<script type=\"module\" src=\"{$fileUrl}\"></script>\n";
                        
                        // Load accompanying CSS files if any
                        if (isset($manifest[$asset]['css'])) {
                            foreach ($manifest[$asset]['css'] as $cssFile) {
                                $cssUrl = asset('build/' . $cssFile);
                                $html .= "<link rel=\"stylesheet\" href=\"{$cssUrl}\">\n";
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($assets as $asset) {
                if (substr($asset, -4) === '.css') {
                    $html .= "<link rel=\"stylesheet\" href=\"" . asset($asset) . "\">\n";
                } else {
                    $html .= "<script type=\"module\" src=\"" . asset($asset) . "\"></script>\n";
                }
            }
        }

        return $html;
    }
}
