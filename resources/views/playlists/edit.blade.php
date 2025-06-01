@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Editar Playlist</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('playlists.update', $playlist) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="name">Nombre de la Playlist</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                name="name" value="{{ old('name', $playlist->name) }}" required autofocus>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_public" id="is_public" 
                                {{ old('is_public', $playlist->is_public) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_public">
                                Playlist pública
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('playlists.show', $playlist) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                Actualizar Playlist
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
