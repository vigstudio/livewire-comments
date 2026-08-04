<?php

namespace Vigstudio\LivewireComments\Http\Livewire\Actions;

trait Alert
{
    public function rendered()
    {
        if (session()->has('alert')) {
            foreach (session('alert') as $alert) {
                $this->dispatch('alert', type: $alert[0], message: $alert[1]);
            }
            session()->forget('alert');
        }
    }
}
