<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', __('Page Builder - :app', ['app' => config('app.name')]))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />


    {{-- Quill is no longer loaded here: the rich text property widget ships it via
         Livewire's @assets, so it also loads on the public page during live edit.
         Hosts that published this layout should drop the CDN tags too, or Quill loads twice. --}}

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

</head>

<body class="h-screen flex flex-col bg-gray-100">
    {{ $slot }}
    @livewireScripts

    @if (app()->environment('local', 'development'))
        {{-- Include safe classes for Tailwind JIT compiler --}}
        @include('page-builder::dev.safe-classes-transforms')
    @endif
</body>

</html>
