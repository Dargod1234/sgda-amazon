<option value="{{ $folder->id }}">{{ str_repeat('— ', $level) }}{{ $folder->nombre }}</option>
@if ($folder->carpetasHijas)
    @foreach ($folder->carpetasHijas as $subfolder)
        @include('partials.folder-option', ['folder' => $subfolder, 'level' => $level + 1])
    @endforeach
@endif
