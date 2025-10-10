@extends('app')

@section('content')
<div class="container" style="max-width: 600px; margin: auto; padding: 20px;">
    <h2>新規投稿</h2>
    <form action="#" method="POST" enctype="multipart/form-data">
        {{-- @csrf <-本番追加 --}}


        <div style="margin-bottom: 15px;">
            <label for="content">投稿内容</label><br>
            <textarea name="content" id="content" rows="4" style="width: 100%;"></textarea>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="image">画像</label><br>
            <input type="file" name="image" id="image">
        </div>

        <button type="submit" style="padding: 10px 20px;">投稿</button>

    </form>
</div>
@endsection