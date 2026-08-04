@if ($this->allow_guest && !$this->auth)
    <div class="vg-composer__guest">
        <div>
            <input wire:model="request.author_name" placeholder="Name" type="text" autocomplete="given-name"
                   @class(['validate-error' => $errors->has('author_name')])>
            @error('author_name')
                <span class="vgcomments_alert_required_text">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <input wire:model="request.author_email" placeholder="Email" type="email" autocomplete="email"
                   @class(['validate-error' => $errors->has('author_email')])>
            @error('author_email')
                <span class="vgcomments_alert_required_text">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <input wire:model="request.author_url" placeholder="Website (optional)" type="url" autocomplete="url"
                   @class(['validate-error' => $errors->has('author_url')])>
            @error('author_url')
                <span class="vgcomments_alert_required_text">{{ $message }}</span>
            @enderror
        </div>
    </div>
@endif
