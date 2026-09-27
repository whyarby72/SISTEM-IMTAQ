<?php

namespace App\Providers;

use App\Domains\Academic\AI\Contracts\AcademicAiModelProvider;
use App\Domains\Academic\AI\Providers\OpenAiResponsesProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AcademicAiModelProvider::class, OpenAiResponsesProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('academic-ai', function (Request $request): Limit {
            return Limit::perMinute((int) config('academic.ai.rate_limit_per_minute', 10))
                ->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('admin-ai-provider', function (Request $request): Limit {
            return Limit::perMinute(12)
                ->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        Blade::directive('uiLabel', function (string $expression): string {
            return "<?php echo e(\\App\\Shared\\Platform\\Presentation\\UiLabel::clean($expression)); ?>";
        });
    }
}
