\# CLAUDE.md — NEXUSPHERE

## このプロダクトは何か
学内の人を「属性（学年・学科・専攻）」と「活動（タイムライン投稿）」で見つけ、外部SNSやポートフォリオに渡す導線サービス。
滞在させるSNSではない。機能を提案する前に「導線に必要か」で判断すること。

## 上流の設計資料（Notion）
- 方針・設計・リスク・決定の理由は Notion の「NEW-NEXUSPHERE」配下にある
- **作業を始める前に、必ず目次ページを読む**: `00 目次と現状サマリ`（ページID `3e2c55298652810d9ba5d320782747f1`）
- 目次から、今のタスクに必要なページだけを開く。全ページを読まない
- タスクと進捗は Notion の To-do データベース（ID `283d66ed6b9b443487da9465d15e04e4`）。タスク名の番号が着手順
- Notion に書いていないことを推測で決めない。らいどに確認する
- Notion への書き込み（To-do の進行状況の更新を含む）は、頼まれたときだけ行う

## リポジトリ構成
- リポジトリルート: `docker-compose.yml`, `docker/`
- Laravel本体: `nexusphere/`（コンテナ内では `/var/www/nexusphere`）
- 開発環境: WSL2 Ubuntu 22.04 上の `~/NEXUSPHERE`

## 環境
- PHP 8.3（Dockerfile）/ Laravel 12 / MySQL（mysql:latest）/ TailwindCSS / 素のJavaScript
- コンテナ: `app-nexus`（PHP-FPM）, `nginx-nexus`, `db-nexus`, `mail-nexus`（キューワーカー。`queue:work` が常駐）
- ローカルURL: http://127.0.0.1:8881

## よく使うコマンド（リポジトリルートで実行）
```bash
docker compose exec -w /var/www/nexusphere app-nexus php artisan <command>
docker compose exec -w /var/www/nexusphere app-nexus php artisan test
docker compose exec -w /var/www/nexusphere app-nexus composer <command>
docker compose exec -w /var/www/nexusphere app-nexus npm run build
```
- nginx-nexus の再起動は `restart` ではなく stop → start（restart だと FastCGI upstream のIPキャッシュが残り通信障害が起きる）
  `docker compose stop nginx-nexus && docker compose start nginx-nexus`
- app-nexus を再起動した後は nginx-nexus も stop → start する
- キューで動く Job / Notification を変更したら mail-nexus を再起動する（queue:work は起動時のコードを保持し続ける）

## 作業の進め方（必ず守る）
1. Notion の目次ページと、タスクに関係するページを読む
2. 実装前に計画を提示し、承認を待つ。承認前にファイルを変更しない
3. 1タスク = 1ブランチ。`develop` から `feature/xxx` または `fix/xxx` を切る
4. テストを先に書き、失敗を確認してから実装する
5. 完了報告には、実行したテストコマンドと結果を含める
6. 頼まれていない変更（リファクタ、整形、依存更新、ついでの改善）はしない。気づいた点は報告だけする
7. セキュリティ・個人情報に関わる変更をした場合は、完了報告でその箇所を明示する

## 禁止事項
- 本番環境・VPS・本番DBへの接続や操作
- `main` / `develop` へのコミット・push、`git push --force`（push とPR作成は人間が行う）
- テストが落ちたときに、テスト側を書き換えて通すこと（テストが誤っていると判断したら理由を説明して確認を取る）
- 適用済みマイグレーションの書き換え。スキーマ変更は必ず新規マイグレーションで行う
- `.env` や秘密情報の読み出し・表示・コミット
- `dms` テーブルの中身（メッセージ本文）の閲覧・出力（通信の秘密）
- 開発用DBに対するテスト実行。テストは専用DB（`.env.testing`）で行う。`RefreshDatabase` は接続先DBを全消去する
- 非表示中の機能（グループDM・サークル・いいね・コメント）の Controller・ルート・テーブル・API の変更。隠すのは画面（Blade・フロントJS）だけ

## 実装時に必ず守るセキュリティ上の制約（詳細は Notion の 02・03）
- 公開URLに `user_id` やそのハッシュを使わない。`user_id` と無関係な乱数トークンを使う
- 公開ページのビューで `users` の本名・メール・学年・学科・専攻を参照しない。学校名も出さない
- 外部URLは `https://` で始まるもののみ許可する
- 公開ページのルートは auth の外に出すが、必ず `throttle` を付ける

## コーディング規約
- PSR-12 と Laravel の慣習に従う
- 主キーは `テーブル単数形_id`（`user_id`, `prc_id`）。モデルで `$primaryKey` を明示し、リレーションではキー名を明示する
  例: `$this->belongsTo(User::class, 'user_id', 'user_id')`
- 命名: クラスは PascalCase / メソッド・変数は camelCase / テーブルは snake_case 複数形 / カラムは snake_case / ルート名はドット区切り / 新規Bladeは camelCase
- boolean は `is` / `has` / `can` で始める
- 早期return・ガード句でネストを浅くする
- マジックナンバー禁止。新規コードは定数か Enum を使う（既存の `prcs.type` の 0/1 は触らない）
- 既存の命名の不統一（`Circle_requests` など）は触らない。新規コードだけ規約に従う
- 処理は Controller に書き込まず Service に切り出す

## コミット
- Conventional Commits: `feat` / `fix` / `docs` /`test`/ `style` / `refactor` / `chore`
- 形式: `type: 変更内容の要約`（日本語、50字以内目安）
- マイグレーションを追加したら README のER図も同じブランチで更新する

## 説明の仕方
- 日本語で回答する
- コマンドや専門用語には、何をするものかを一言添える
- 概念を説明するときは、そもそも何のためにあるのかから説明する