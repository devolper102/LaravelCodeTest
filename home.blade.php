@extends('layouts.plain')

@section('content')
    @include('partials.header')
    @include('partials.banners.trustpilot',[
        'trustpilot' => $content->trustpilot[0] ?? false
    ])
    @component('partials.banners.stats',[
        'entries' => $site->content->statsItems ?? [],
        'heading' => $site->content->statsHeading ?? [],
        'text' => $site->content->statsText ?? '',
        'metadata' => $site->metadata->pluck('value','key') ?? [],
    ])@endcomponent
    @component('partials.elements.linkBoxes',[
        'text' => $content->text,
        'linkBoxes' => $content->programLinks ?? [],
    ])@endcomponent
    @component('partials.banners.list',[
        'entries' => $site->content->clients ?? [],
        'class' => 'clients',
        'source' => $site->files->keyBy('id')
    ])@endcomponent
    @component('partials.sections.successStories',[
        'heading' => $content->successStoriesTitle ?? '',
        'entries' => $content->successStories ?? [],
    ])@endcomponent

    <div class="page page--light-gray">
        <div class="text">
            {!! $content->whoWeAre  !!}
        </div>
    </div>

    @php
        $teamMembers = collect($content->team)->map(function($member){
            return \App\Models\Page::firstCached($member->url);
        });
    @endphp

    @component('partials.sections.teamMembers', [
        'title' => 'Our Experts',
        'members' => $teamMembers,
        'link' => '/ExecutiveTeam.html'
    ])
        <a href="/ExecutiveTeam.html" class="team__member">
            <span class="team__member-image team__member-plus"></span>
            <span class="team__member-plus-text">Meet Our Experts</span>
        </a>
    @endcomponent

    @component('partials.elements.givingBack',[
            'heading' => $content->givingBackTitle ?? '',
            'description' => $content->givingBackDescription ?? '',
            'items' => $content->givingBackItems ?? [],
            'files' => $files
    ])@endcomponent

    @include('partials.footer')
@endsection