@component('mail::message')
{{ $user->name }} さん

以下のボタンをクリックしてメールアドレスを確認してください。

@component('mail::button', ['url' => route('verification.verify', ['user_id' => $user->user_id, 'hash' => sha1($user->mail)])])
メールアドレスを確認する
@endcomponent

ありがとうございました。
@endcomponent