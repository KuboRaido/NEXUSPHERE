# NEXUSPHERE
**学内の人を「属性」と「活動」で見つけ、外部SNSやポートフォリオへつなぐ導線サービス**
### デモ環境URL
https://nexupshere-dev.top
ログインアカウント
メールアドレス　a@sba.ac.jp
パスワード　　　00000000

## 1. プロジェクト概要
NEXUSPHERE（ネクスフィア）は、学内の人を属性（学年・学科・専攻）と活動（タイムライン投稿）で見つけ、その人の外部SNSやポートフォリオへ渡すためのサービスです。
ユーザーを滞在させるSNSではなく、学内で出会うきっかけを作り、つながった後は各自が普段使っているツールへ移ってもらうことを目的にしています。

## 2. 開発背景
* **きっかけ**: 2年次のチーム開発の授業で、4人チームで開発を開始。同じキャンパスにいても、学科や専攻が違うだけで協力相手を探せない「学科間の分断」を解決したかった。
* **方針転換**: 当初はサークル・グループDM・いいね・コメントを備えた滞在型SNSとして作ったが、試験運用ではほとんど使われなかった。そこで「何のためのSNSか」を問い直し、学内の人を外部へつなぐ導線サービスに定義し直した。
* **現在の目標**: 企画・DB設計・コンテナ化・本番運用までを一人で回しながら、導線として機能するかを限定公開で検証する。

## 3. 技術スタック
| カテゴリ | 技術選定 | 選定理由 |
| :--- | :--- | :--- |
| **Backend** | PHP 8.3 / Laravel 12 | Service層・Enum・キューなど、フレームワークの機能で堅牢に書ける。 |
| **Frontend** | JavaScript / CSS | 画面ごとのCSSと素のJavaScriptで構成。 |
| **Database** | MySQL | 外部キー制約とインデックスでデータ整合性を担保。 |
| **Test** | Pest | 既存機能と公開サイトのServiceをテストで保護。 |
| **Infrastructure** | Docker / Docker Compose | 開発環境と本番環境を同じ構成で再現。 |
| **Server** | ConoHa VPS | Docker・php.ini・DNSを自分で制御でき、月¥880固定で運用できるため。 |

## 4. 機能
### 認証・プロフィール
* **新規登録**: 学内ドメインのメールアドレスのみ登録可能。学年・学科・専攻を登録。
* **プロフィール**: 名前・学年・学科・専攻・アイコンを管理。他の学生のプロフィールからDMを送信可能。
* **ユーザー検索**: 名前・学年・学科・専攻のキーワードで部分一致検索。

### タイムライン
* テキスト（最大1000字）と画像（1枚最大5MB）・動画（最大50MB）の投稿。
* 投稿本文のキーワード検索。

### ダイレクトメッセージ（1対1）
* テキスト（最大5000字）と画像・動画の添付（1ファイル最大50MB）。
* 一定間隔のポーリングで新着メッセージを反映。既読管理あり。

### 運用・安全性
* **NGワードフィルタリング**: 自作のバリデーションルールで不適切な投稿を弾く。

### 非表示にした機能
方針転換に合わせ、グループDM・サークル・いいね・コメントは画面から非表示にしています（サーバー側の実装とテーブルは残っています）。

### 開発中
* **公開サイト**: 学生が企業に渡せるポートフォリオURLを持てる機能。外部URLの登録か、テンプレート入力の2方式。乱数トークンのURL・リンク限定公開・noindex・規約同意の記録で設計。

## 5. 設計・データベース
## ER図
``` mermaid
erDiagram

    %% USERS のリレーション
    USERS ||--o{ CIRCLES : "creates (owner_id)"
    USERS ||--o{ DMS : "sends (sender_id)"
    USERS ||--o{ DMS : "receives (receiver_id)"
    USERS ||--o{ DM_READS : "has (user_id)"
    USERS ||--o{ DM_READS : "read as (partner_id)"
    USERS ||--o{ PRCS : "posts"
    USERS ||--o{ NICES : "gives"
    USERS ||--o{ GROUPMEMBERS : "joins"
    USERS ||--o{ CIRCLE_USERS : "joins"
    USERS ||--o{ CIRCLE_REQUESTS : "requests"
    USERS ||--|| PROFILES : "has"
    USERS ||--|| CUSTOMS : "has"
    USERS ||--o| PORTFOLIO_SITES : "has"
    USERS ||--o{ LOGIN_HISTORIES : "has"
    USERS ||--o{ WEB_PUSH_SUBSCRIPTIONS : "has"

    %% 学科・専攻のマスタ
    SUBJECTS ||--o{ MAJORS : "has"
    SUBJECTS ||--o{ USERS : "belongs (subject_id)"
    MAJORS ||--o{ USERS : "belongs (major_id)"

    %% CIRCLES のリレーション
    CIRCLES ||--o{ GROUPS : "has"
    CIRCLES ||--o{ DMS : "context for"
    CIRCLES ||--o{ PRCS : "context for"
    CIRCLES ||--o{ CIRCLE_USERS : "has members"
    CIRCLES ||--o{ CIRCLE_REQUESTS : "receives"
    CIRCLES ||--o{ DM_READS : "context for"

    %% GROUPS のリレーション
    GROUPS ||--o{ DMS : "context for"
    GROUPS ||--o{ GROUPMEMBERS : "has members"
    GROUPS ||--o{ DM_READS : "context for"

    %% PRCS のリレーション
    PRCS ||--o{ IMAGES_AND_VIDEOS : "has"
    PRCS ||--o{ NICES : "receives"
    PRCS ||--o{ PRCS : "parent/child"

    %% DMS のリレーション
    DMS ||--o{ IMAGES_AND_VIDEOS : "has"
    DMS ||--o{ DMS : "parent/child"

    %% ==========================================
    %% テーブル定義（カラム詳細）
    %% ==========================================

    USERS {
        bigint user_id PK
        string mail "unique"
        string password
        text name
        string job "default: 学生"
        int grade "nullable"
        bigint subject_id FK "nullable"
        bigint major_id FK "nullable"
        text subject "nullable (旧カラム)"
        text major "nullable (旧カラム)"
        text icon "nullable"
        string remember_token "nullable"
        timestamp email_verified_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    SUBJECTS {
        bigint subject_id PK
        string subject_name "unique"
    }

    MAJORS {
        bigint major_id PK
        bigint subject_id FK
        string major_name "unique (subject_id, major_name)"
    }

    PORTFOLIO_SITES {
        bigint portfolio_site_id PK
        bigint user_id FK "unique"
        string token "unique, 16文字"
        string site_type "template / external"
        string external_url "nullable"
        boolean is_public "default: false"
        timestamp agreed_at "nullable"
        string terms_version "nullable"
        string display_name "nullable"
        text bio "nullable"
        json links "nullable"
        timestamp created_at
        timestamp updated_at
    }

    WEB_PUSH_SUBSCRIPTIONS {
        bigint id PK
        bigint user_id FK
        text endpoint
        text p256dh
        text auth
        timestamp last_used_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    CIRCLES {
        bigint circle_id PK
        string circle_name "unique"
        int owner_id FK "Users(user_id)"
        string category "nullable"
        string sentence
        string icon "nullable, unique"
        int members_count
        timestamp created_at
        timestamp updated_at
    }

    GROUPS {
        bigint group_id PK
        string group_name "unique"
        bigint circle_id FK "nullable"
        text icon "nullable"
        int members_count
        timestamp created_at
        timestamp updated_at
    }

    DMS {
        bigint dm_id PK
        string dm_key "nullable"
        bigint circle_id FK "nullable"
        bigint group_id FK "nullable"
        bigint sender_id FK
        bigint receiver_id FK "nullable"
        bigint reply_to_dm_id FK "nullable"
        bigint conversation_id "nullable"
        text message_text "nullable"
        json attachments "nullable"
        bigint user_id FK "Users(user_id)"
        bigint parent_id FK "nullable"
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at "nullable (Soft Delete)"
    }

    DM_READS {
        bigint id PK
        bigint user_id FK
        bigint partner_id FK "nullable"
        bigint circle_id FK "nullable"
        bigint group_id FK "nullable"
        timestamp last_read_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    PRCS {
        bigint prc_id PK
        bigint user_id FK "nullable"
        bigint circle_id FK "nullable"
        bigint profile_id FK "nullable"
        int type "nullable"
        int parent_id FK "nullable"
        text sentence "nullable"
        timestamp created_at
        timestamp updated_at
    }

    IMAGES_AND_VIDEOS {
        bigint image_and_video_id PK
        bigint prc_id FK "nullable"
        bigint dm_id FK "nullable"
        text video "nullable"
        text image "nullable"
        timestamp created_at
        timestamp updated_at
    }

    NICES {
        bigint nice_id PK
        int prc_id FK
        int user_id FK
        timestamp created_at
        timestamp updated_at
    }

    GROUPMEMBERS {
        bigint groupmember_id PK
        bigint user_id FK
        bigint group_id FK
        timestamp created_at
        timestamp updated_at
    }

    CIRCLE_USERS {
        bigint circle_user_id PK
        bigint circle_id FK
        bigint user_id FK "nullable"
        timestamp created_at
        timestamp updated_at
    }

    CIRCLE_REQUESTS {
        bigint circle_request_id PK
        bigint circle_id FK
        bigint user_id FK
        string status "pending/approved/rejected"
        timestamp request_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    PROFILES {
        bigint profile_id PK
        bigint user_id FK
        timestamp created_at
        timestamp updated_at
    }

    CUSTOMS {
        bigint custom_id PK
        int user_id FK "unique"
        timestamp created_at
        timestamp updated_at
    }

    LOGIN_HISTORIES {
        bigint id PK
        bigint user_id FK
        string ip_address "nullable"
        string user_agent "nullable"
        timestamp login_at
    }
```
CIRCLES・GROUPS・GROUPMEMBERS・CIRCLE_USERS・CIRCLE_REQUESTS・NICES は、画面を非表示にした機能のテーブルです。

## 6. セットアップ
```bash
# リポジトリのクローン
git clone https://github.com/KuboRaido/NEXUSPHERE.git
cd NEXUSPHERE

# コンテナの起動
docker compose up -d

# 環境設定（DB接続は下の値に書き換える）
docker compose exec -w /var/www/nexusphere app-nexus cp .env.example .env
#   DB_CONNECTION=mysql
#   DB_HOST=db-nexus
#   DB_PORT=3306
#   DB_DATABASE=platform
#   DB_USERNAME=dbuser
#   DB_PASSWORD=dbuser

# 依存パッケージのインストールとビルド
docker compose exec -w /var/www/nexusphere app-nexus composer install
docker compose exec -w /var/www/nexusphere app-nexus npm install
docker compose exec -w /var/www/nexusphere app-nexus npm run build

# アプリキーの生成・DBマイグレーション・アップロード画像の公開設定
docker compose exec -w /var/www/nexusphere app-nexus php artisan key:generate
docker compose exec -w /var/www/nexusphere app-nexus php artisan migrate --seed
docker compose exec -w /var/www/nexusphere app-nexus php artisan storage:link
```
起動後は http://127.0.0.1:8881 で開けます。

## 7. 今後の予定
* **公開サイト**: Controller・画面・テストを実装し、本番で公開。
* **メール認証の強化**: 確認リンクを署名付きURLにし、未認証ユーザーのログインを拒否。
* **DM受信のメール通知**。
* **CI**: GitHub Actionsでテストと静的解析（PHPStan）を自動実行。
* **限定公開**: 10人×2週間の限定公開で、導線として機能するかを検証。