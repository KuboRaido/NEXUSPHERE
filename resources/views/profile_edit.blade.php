@extends('layouts.app')

@section('content')
<div class="container">
    <h2>プロフィールを編集</h2>

    {{-- エラーメッセージ --}}
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 成功メッセージ --}}
    @if (session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div>
            <label for="department">学科:</label>
            <input type="text" name="department" value="{{ old('department', $user->department) }}">
        </div>

        <div>
            <label for="major">専攻:</label>
            <input type="text" name="major" value="{{ old('major', $user->major) }}">
        </div>

        <div>
            <label for="icon">アイコン画像:</label>
            <input type="file" name="icon">
            @if ($user->icon)
                <div>
                    <p>現在のアイコン:</p>
                    <img src="{{ asset('storage/' . $user->icon) }}" alt="現在のアイコン" style="width: 100px; height: 100px; object-fit: cover;">
                </div>
            @endif
        </div>

        <button type="submit">更新</button>
    </form>

    <p><a href="{{ route('profile.show') }}">戻る</a></p>
</div>
@endsection
