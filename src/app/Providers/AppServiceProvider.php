<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrap();

        Blade::directive('timeHmSec', function ($expression) {
            return "<?php
                \$__t = {$expression};
                \$__s = \$__t === null || \$__t === '' ? '00:00:00' : (is_object(\$__t) ? \$__t->format('H:i:s') : (string) \$__t);
                \$__p = explode(':', \$__s);
                echo '<span class=\"time-hm\">' . (\$__p[0] ?? '00') . ':' . (\$__p[1] ?? '00') . '</span><span class=\"time-seconds\">' . (isset(\$__p[2]) ? ':' . \$__p[2] : '') . '</span>';
            ?>";
        });
    }
}