<?php

namespace Vigstudio\LivewireComments;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;

class LivewireCommentsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'livewire-comments');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        Config::set(['livewire.temporary_file_upload.rules' => Config::get('vgcomment.upload_rules')]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerBladeDirectives();
        $this->registerServices($this->app);
        $this->definePublishing();
    }

    /**
     * @return void
     */
    protected function definePublishing()
    {
        // Publishing the views.
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/livewire-comments'),
        ], 'vgcomment-views-livewire');

        // Publishing assets.
        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/livewire-comments'),
        ], 'vgcomment-assets-livewire');
    }

    /**
     * @return void
     */
    protected function registerBladeDirectives()
    {
        Blade::directive('commentStyles', function ($expression) {
            return "<?php echo '<link href=\"'.e(asset('vendor/livewire-comments/css/comments.css')).'?v='.e(@filemtime(public_path('vendor/livewire-comments/css/comments.css')) ?: time()).'\" rel=\"stylesheet\">'; ?>";
        });

        Blade::directive('commentScripts', function ($expression) {
            return "<?php \$script = '<script src=\"https://www.google.com/recaptcha/api.js?render='.e(\\Illuminate\\Support\\Facades\\Config::get('vgcomment.recaptcha_key')).'\"></script>'; \$script .= '<script src=\"'.e(asset('vendor/livewire-comments/js/comments.js')).'?v='.e(@filemtime(public_path('vendor/livewire-comments/js/comments.js')) ?: time()).'\"></script>'; echo \$script; ?>";
        });
    }

    /**
     * @return void
     */
    protected function registerServices($app)
    {
        //register
        $app->register(LivewireServiceProvider::class);
    }
}
