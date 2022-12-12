<?php

namespace Vigstudio\LivewireComments\Http\Livewire\Actions;

trait Alert
{
    public function dehydrate()
    {
        if (session()->has('alert')) {
            foreach (session('alert') as $alert) {
                $this->emit('alert', [$alert[0], $alert[1]]);
            }
            session()->forget('alert');
        }
    }
}
