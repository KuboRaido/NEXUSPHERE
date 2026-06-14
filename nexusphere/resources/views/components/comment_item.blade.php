<div class="comment">
    <div class="comment_head">
        <a href="{{ $comment->user->user_id === auth()->id() ? route('profile') : route('profile.other', $comment->user->user_id)}}" class="user-link">
            <img src="{{ $comment->user->avatar_url }}" class="user-icon small">
            <strong class="user_name">{{ $comment->user->name }}</strong>
        </a>
        @if ($comment->created_at->gt(now()->subWeek()))
                <time class="comment_time">{{ $comment->created_at->diffForHumans() }}</time>
        @else
                <time class="comment_time">{{ $comment->created_at->format('Y年m月d日') }}</time>
        @endif
    </div>
    <span class="comment-text">{!! \App\Support\TextHelper::linkify($comment->sentence ?? '') !!}</span>
</div>