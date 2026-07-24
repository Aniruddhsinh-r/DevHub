<x-filament-widgets::widget>
    <x-filament::section heading="Engagement Stats">
        <div style="display:flex; flex-direction:column; gap:16px;">

            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; background:rgba(255, 255, 255, 0.05); border-radius:8px; border:1px solid rgba(255, 255, 255, 0.08);">
                <span style="font-size:14px; color:#9ca3af; font-weight:500;">Total Comments</span>
                <span style="font-size:18px; font-weight:700; color:#f59e0b;">{{ $comments }}</span>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; background:rgba(255, 255, 255, 0.05); border-radius:8px; border:1px solid rgba(255, 255, 255, 0.08);">
                <span style="font-size:14px; color:#9ca3af; font-weight:500;">Total Likes</span>
                <span style="font-size:18px; font-weight:700; color:#f59e0b;">{{ $likes }}</span>
            </div>

            <hr style="border:0; border-top:1px solid rgba(255, 255, 255, 0.1); margin:4px 0;">

            @if ($author)
                <div>
                    <h3 style="font-size:14px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#9ca3af; margin:0 0 12px 0;">Top Author</h3>
                    <hr style="border:0; border-top:1px solid rgba(255, 255, 255, 0.1); margin:4px 0;padding-bottom:15px;">
                    <div style="display:flex; align-items:center; gap:20px;">
                        <img src="{{ $author->avatar ? asset('storage/' . $author->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($author->name) }}"
                            style="width:80px; height:80px; border-radius:9999px; object-fit:cover; flex-shrink:0;">

                        <div style="flex:1;">
                            <h2 style="font-size:20px; font-weight:700; margin:0; color: #ffffff;">{{ $author->name }}</h2>
                            <p style="margin:4px 0; color:#9ca3af; font-size:14px;">{{ $author->email }}</p>
                            <p style="margin:4px 0; font-weight:600; font-size:16px; color:#ffffff;">{{ $author->articles_count }} Articles</p>
                            <a href="{{ \App\Filament\Resources\Users\UserResource::getUrl('view', ['record' => $author]) }}"
                                style="display:inline-block; margin-top:8px; padding:8px 14px; background:#f59e0b;
                                color:white; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">View Profile</a>  
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>