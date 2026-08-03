@if ($this->allow_guest && !$this->auth)
    <div class="vcomments__form__guest">

        <input wire:model="request.author_name" placeholder="Name" type="text" autocomplete="given-name" class="vgcomments__form__guest__input">

        <input wire:model="request.author_email" placeholder="Email" type="email" autocomplete="family-name" class="vgcomments__form__guest__input">

        <input wire:model="request.author_url" placeholder="Url" type="text" autocomplete="email" class="vgcomments__form__guest__input">

    </div>
@endif
