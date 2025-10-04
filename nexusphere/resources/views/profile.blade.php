<div class="container">
    <h2>{{ $user->name }} さんのプロフィール</h2>

    {{-- プロフィール画像 --}}
    @if ($user->icon)
        <img src="{{ asset('storage/' . $user->icon) }}" alt="プロフィール画像" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
    @else
        <p>アイコン未設定</p>
    @endif

    {{-- ユーザー基本情報 --}}
    <ul>
        <li><strong>学年：</strong> {{ $user->grade ?? '未設定' }}</li>
        <li><strong>学科：</strong> {{ $user->department ?? '未設定' }}</li>
        <li><strong>専攻：</strong> {{ $user->major ?? '未設定' }}</li>
    </ul>

    <a href="{{ route('profile') }}">プロフィールを編集する</a>

    <hr>

    <h3>投稿一覧</h3>

    @forelse ($posts as $post)
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
            <p>{{ $post->content }}</p>
            <small>投稿日: {{ $post->created_at->format('Y-m-d H:i') }}</small>
        </div>
    @empty
        <p>まだ投稿はありません。</p>
    @endforelse
</div>
@endsection
