@extends('adminlte::page')

@section('title', 'Language Management')

@section('content_header')
    <h1>Language Management</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Available Languages</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Language Code</th>
                                    <th>Language Name</th>
                                    <th>Direction</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $languageNames = [
                                        'en' => 'English',
                                        'fa' => 'Persian (Farsi)',
                                        'ar' => 'Arabic',
                                        'fr' => 'French',
                                        'de' => 'German',
                                        'es' => 'Spanish',
                                        'it' => 'Italian',
                                        'pt' => 'Portuguese',
                                        'ru' => 'Russian',
                                        'zh' => 'Chinese',
                                        'ja' => 'Japanese',
                                        'ko' => 'Korean',
                                        'tr' => 'Turkish',
                                        'nl' => 'Dutch',
                                    ];
                                    
                                    $rtlLanguages = ['fa', 'ar', 'he', 'ur'];
                                @endphp
                                
                                @foreach($languages as $lang)
                                    <tr>
                                        <td>{{ $lang }}</td>
                                        <td>{{ $languageNames[$lang] ?? $lang }}</td>
                                        <td>{{ in_array($lang, $rtlLanguages) ? 'RTL' : 'LTR' }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.language.use', ['locale' => $lang]) }}" class="btn btn-sm btn-success">
                                                    Use
                                                </a>
                                                <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown">
                                                    Edit
                                                </button>
                                                <div class="dropdown-menu">
                                                    @foreach($languageFiles as $file)
                                                        <a class="dropdown-item" href="{{ route('admin.language.edit', ['lang' => $lang, 'file' => $file]) }}">
                                                            {{ ucfirst($file) }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Language Settings</h3>
                </div>
                <div class="card-body">
                    <h5>Auto Detect & Translate</h5>
                    <form action="{{ route('admin.language.auto-translate') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Source Language</label>
                            <select name="source_lang" class="form-control">
                                @foreach($languages as $lang)
                                    <option value="{{ $lang }}" {{ $lang === 'en' ? 'selected' : '' }}>
                                        {{ $languageNames[$lang] ?? $lang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Target Language</label>
                            <select name="target_lang" class="form-control">
                                @foreach($languages as $lang)
                                    <option value="{{ $lang }}" {{ $lang === 'fa' ? 'selected' : '' }}>
                                        {{ $languageNames[$lang] ?? $lang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>File</label>
                            <select name="file" class="form-control">
                                @foreach($languageFiles as $file)
                                    <option value="{{ $file }}">{{ ucfirst($file) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Auto Translate Missing Strings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="{{ asset('js/adminlte-dark-mode.js') }}"></script>
@stop 