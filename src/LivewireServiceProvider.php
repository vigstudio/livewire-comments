<?php

namespace Vigstudio\LivewireComments;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Vigstudio\LivewireComments\Http\Livewire\CommentsComponent;
use Vigstudio\LivewireComments\Http\Livewire\FormComponent;

class LivewireServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Livewire::component('livewire-comments::comments', CommentsComponent::class);
        Livewire::component('livewire-comments::form', FormComponent::class);
    }
}
