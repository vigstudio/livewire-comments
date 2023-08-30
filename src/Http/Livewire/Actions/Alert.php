<?php

namespace Vigstudio\LivewireComments\Http\Livewire\Actions;

trait Alert
{
    public function rendered()
    {
        if (session()->has('alert')) {
            foreach (session('alert') as $alert) {
                $this->dispatch('alert', $alert[0], $alert[1]);
            }
            session()->forget('alert');
        }
    }
}
