@props(['post','deletePost','is_my_post' => true])
<div {{ $attributes -> merge(['class' => 'post']) }} data-post-id="{{ $post->prc_id }}">

            {{-- 投稿者名 --}}
            <div class="post-header">
                <a href="{{ $post->user->user_id === auth()->id() ? route('profile') : route('profile.other', $post->user->user_id)}}" class="user-link">
                    <img src="{{ $post->user->avatar_url }}"
                        class="user-icon"
                        alt="icon"></img>
                    <span class="username">{{ $post->user->name }}</span>
                </a>

                {{-- 右上のメタ情報（削除ボタン＋日時） --}}
                <div class="post-meta">
                    @if( $deletePost && $is_my_post)
                        <button id="delete-post-trigger-{{ $post->prc_id }}" class="delete-post-trigger post-delete-btn" data-post-id="{{ $post->prc_id }}">削除</button>
                    @endif
                    
                    @if ($post->created_at->gt(now()->subWeek()))
                        <time class="post-time-text">{{ $post->created_at->diffForHumans() }}</time>
                    @else
                        <time class="post-time-text">{{ $post->created_at->format('Y年m月d日') }}</time>
                    @endif
                </div>

                @if($deletePost && $is_my_post)
                    <div id="delete-post-confirm-{{ $post->prc_id }}" class="delete-post-confirm" hidden>
                        <div class="delete-post-dialog">
                            <p>この投稿を削除しますか？</p>
                            <div class="delete-post-actions">
                                {{-- 削除用フォーム（ルート設定が必要です） --}}
                                <form method="POST" action="/post/{{ $post->prc_id }}/delete" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-post-yes">はい</button>
                                </form>
                                <button type="button" class="delete-post-no" data-post-id="{{ $post->prc_id }}">いいえ</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 投稿内容 --}}
            <div class="post-content">{!! \App\Support\TextHelper::linkify($post->sentence ?? '') !!}</div>

            {{-- メディア (画像 or 動画) --}}
            @if ($post->images && $post->images->count() > 0)
                <div class="post-images">
                    @foreach ($post->images as $media)

                        @php
                            // 画像と動画のどちらを使うか判定
                            $filePath = $media->image ?? $media->video;

                            // null回避

                            // 拡張子取得
                            $exetension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                        @endphp

                        {{-- 画像ならimgタグ --}}
                        @if (in_array($exetension, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                            <img src="{{ asset('storage/post/' . $filePath) }}"
                                alt="投稿画像"
                                class="post-image"
                                onclick="openModal(this.src)">
                        @endif

                        {{-- 動画ならvideoタグ --}}
                        @if (in_array($exetension, ['mp4', 'mov', 'webm']))
                            <video controls
                                class="post-video"
                                style="max-width: 100%; border-radius: 8px; margin-top: 10px;">
                                <source src="{{ asset('storage/post/' . $filePath) }}" type="video/{{ $exetension }}">
                                お使いのブラウザは動画再生に対応していません。
                            </video>
                        @endif

                    @endforeach
                </div>
            @endif

            <div class="post-footer">

                {{-- いいね --}}
                @php $liked = $post->nices->contains('user_id', auth()->id()); @endphp
                <form method="POST" action="/posts/{{ $post->prc_id }}/like" class="js-like-form">
                    @csrf
                    <button type="submit" class="like-button {{ $liked ? 'liked' : '' }}">
                        <i class="{{ $liked ? 'fa-solid' : 'fa-regular' }} fa-heart like-icon"></i>
                    </button>
                    <button type="button" data-url="/post/{{$post->prc_id}}/likes" class="like-count-btn js-like-users-trigger">
                        <span class="like-count">{{ $post->nices->count() }}</span>
                    </button>
                </form>
                {{-- コメント入力 --}}
                <form method="POST" action="/posts/{{ $post->prc_id }}/comment" class="comment-form">
                    @csrf
                    <input type="text" name="comment" class="comment-input"placeholder="コメントを追加" required>
                    <button type="submit">送信</button>
                </form>

            </div>


                {{-- コメント一覧 --}}
                    <div class="comment_list">
                        @foreach ($post->comments as $comment)
                            <x-comment_item :comment="$comment"  />
                        @endforeach
                    </div>
                @if($post->comments->count() > 3)
                    <button class="showMoreBtn">全てのコメントを見る</button>
                @endif
</div>