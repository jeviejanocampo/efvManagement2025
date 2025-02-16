<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Products;
use App\Models\Variant;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
        {
            View::composer('*', function ($view) {
                // Count low stock products
                $lowStockProductsCount = Products::whereBetween('stocks_quantity', [0, 5])->count();

                // Count low stock variants
                $lowStockVariantsCount = Variant::whereBetween('stocks_quantity', [0, 5])->count();

                // Combine the counts
                $lowStockCount = $lowStockProductsCount + $lowStockVariantsCount;

                // Share with all views
                $view->with('lowStockCount', $lowStockCount);
            });
        }

    
}
