<?php

namespace Vigstudio\LivewireComments;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Livewire 4: namespaced package components use addNamespace (`::`).
        // Explicit Livewire::component('pkg::name') is ignored for `::` names.
        Livewire::addNamespace(
            namespace: 'livewire-comments',
            classNamespace: 'Vigstudio\\LivewireComments\\Http\\Livewire',
            classPath: __DIR__ . '/Http/Livewire',
            classViewPath: __DIR__ . '/../resources/views/livewire',
        );
    }
}
