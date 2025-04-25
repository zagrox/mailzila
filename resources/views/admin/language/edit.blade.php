@extends('adminlte::page')

@section('title', 'Edit Language Strings')

@section('content_header')
    <h1>Edit Language Strings - {{ $lang }} / {{ $file }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-right">
                <a href="{{ route('admin.language.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Languages
                </a>
            </div>
            <h3 class="card-title">Translation Editor</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('info'))
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-info"></i> Info!</h5>
                    {{ session('info') }}
                </div>
            @endif
            
            <form action="{{ route('admin.language.update') }}" method="POST">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">
                <input type="hidden" name="file" value="{{ $file }}">
                
                <div class="form-group">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search for keys or values...">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="translationsTable">
                        <thead>
                            <tr>
                                <th width="40%">Key</th>
                                <th width="60%">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($translations as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $subKey => $subValue)
                                        <tr>
                                            <td>
                                                <code>{{ $key }}.{{ $subKey }}</code>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="translations[{{ $key }}][{{ $subKey }}]" value="{{ $subValue }}" dir="{{ $lang === 'fa' || $lang === 'ar' ? 'rtl' : 'ltr' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>
                                            <code>{{ $key }}</code>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="translations[{{ $key }}]" value="{{ $value }}" dir="{{ $lang === 'fa' || $lang === 'ar' ? 'rtl' : 'ltr' }}">
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('admin.language.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="{{ asset('js/adminlte-dark-mode.js') }}"></script>
    <script>
        $(function() {
            // Search functionality
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#translationsTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
@stop 