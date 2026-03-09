# NEXUSPHERE
**学生の専門性を掛け合わせ、学科を越えた共創を生むSNSプラットフォーム**

## 1. プロジェクト概要
NEXUSPHERE（ネクスフィア）は、学生特有のコミュニティ形成と情報交換を最適化するためのSNSです。
既存のSNSでは困難だった「スキルベースでの他学科ユーザー探索」を可能にし、エンジニア、デザイナー、クリエイターが所属の枠を超えて繋がるきっかけを提供します。

## 2. 開発背景（解決したい課題）
* **課題**: 「アプリを作りたいエンジニア」と「UIをデザインしたい学生」が同じキャンパスにいても、学科が違うだけで接点が持てないという**「学科間の分断」**。
* **解決策**: 属性（学年・学科）に特化した検索・プロフィール機能の実装により、効率的なネットワーキングを実現。
* **目標**: 企画からデータベース設計、コンテナ化、本番デプロイまでのフルサイクル開発。

## 3. 技術スタック
| カテゴリ | 技術選定 | 選定理由 |
| :--- | :--- | :--- |
| **Backend** | PHP 8.2 / Laravel 11.x | 最新のフレームワーク機能を活用した迅速かつ堅牢な開発。 |
| **Frontend** | JavaScript / Tailwind CSS | ユーティリティファーストによるレスポンシブUIの高速実装。 |
| **Database** | MySQL 8.0 | 外部キー制約とインデックス最適化によるデータ整合性の担保。 |
| **Infrastructure** | Docker / Docker Compose | 開発環境のコンテナ化による再現性の確保。 |
| **Server** | Lolipop! | 画像・動画投稿の容量を重視しつつ、コストパフォーマンスを両立。|
## 4. 機能詳細と技術的なこだわり
### 🔐 認証・ユーザー管理
* **新規登録**: 学内メールアドレス（ドメイン制限）による関係者限定の登録フロー。学年・学科・専攻の属性情報を保持。
* **ログイン**: 評価用ゲストアカウントを設置（詳細は後述）。
* **プロフィール**: 自己紹介、スキル、ポートフォリオURLの管理。自身へのDM送信やプロフィール編集が可能。

### 📱 コミュニケーション
* **ホーム（タイムライン）**: 全ユーザーの投稿を閲覧、検索、リアクション（いいね・コメント）。
* **ダイレクトメッセージ（DM）**: 
    * 1対1およびグループ形式のリアルタイムに近いチャット。
    * 画像・動画の添付送信（画像: 最大5MB / 動画: 最大500MB）。
    * ユーザー名による宛先検索および既読管理。
* **サークル機能**: 
    * 共通の目的を持つ学生同士のコミュニティ作成。
    * 参加承認制（オーナーによる申請承諾・拒否）の実装。
    * サークル内限定の掲示板およびトークルーム。
### 🛡️ 運用を見据えた堅牢性
* **学内限定認証**: 特定ドメインのメールアドレスのみ登録を許可し、クローズドなコミュニティの安全性を担保。
* **NGワードフィルタリング**: 自作のバリデーションルールにより、不適切な投稿を自動検閲。
* **フルアクセスロギング**: ミドルウェアにより、全ての操作ログ（URL、メソッド、IP、UA）をDBに記録。

### 🤝 コミュニティ・共創支援
* **サークル・グループ機能**: 
    * 属性に応じたサークル作成および参加承認フローの実装。
    * サークル内メンバー限定のグループDMおよびファイル添付機能。
* **詳細な検索システム**: 投稿内容、ユーザー名、サークル名のキーワード検索。

### 🎨 ユーザー体験 (UX)
* **メディアアップロード**: 最大500MBの動画および5MBの画像投稿に対応。
* **プロフィールカスタマイズ**: ユーザーごとにヘッダーや背景色のテーマ設定を保持。
## 5. 評価用アカウント
環境構築なしで動作を確認するためのデモアカウントです。
- **URL**: `https://nexuspheres.com/newlogin`
- **ログイン情報**:
  | 役割 | メールアドレス | パスワード |
  | :--- | :--- | :--- |
  | テストユーザーA | a@sba.ac.jp | 00000000 |
  | テストユーザーB | b@sba.ac.jp | 00000000 |
## 6. 設計・データベース
スケーラビリティとデータ整合性を重視し、第3正規化までを徹底した設計を行っています。
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
    USERS ||--o{ LOGIN_HISTORIES : "has"
    USERS ||--o{ ACCESS_LOGS : "has"

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
    
    %% DMS のリレーション
    DMS ||--o{ IMAGES_AND_VIDEOS : "has"

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
        text subject "nullable"
        text major "nullable"
        text icon "nullable"
        timestamp email_verified_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    CIRCLES {
        bigint circle_id PK
        string circle_name "unique"
        int owner_id FK "Users(user_id)"
        string category "nullable"
        string sentence
        string icon "nullable"
        int members_count
        timestamp created_at
        timestamp updated_at
    }

    GROUPS {
        bigint group_id PK
        string group_name "unique"
        bigint circle_id FK "nullable"
        string icon "nullable"
        int members_count
        timestamp created_at
        timestamp updated_at
    }

    DMS {
        bigint dm_id PK
        string dm_key "unique"
        bigint circle_id FK "nullable"
        bigint group_id FK "nullable"
        bigint sender_id FK
        bigint receiver_id FK "nullable"
        bigint reply_to_dm_id FK "nullable"
        bigint conversation_id 'nullable'
        text message_text "nullable"
        json attachments "nullable"
        timestamp read_at "nullable"
        boolean is_read 
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
        bigint circle_id FK "nullable"
        bigint user_id FK "nullable"
        timestamp created_at
        timestamp updated_at
    }

    CIRCLE_REQUESTS {
        bigint id PK
        bigint circle_id FK
        bigint user_id FK
        string status "pending/approved/rejected"
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
        bigint id PK
        int user_id FK "unique"
        string background_color
        string header_color
        timestamp created_at
        timestamp updated_at
    }

    LOGIN_HISTORIES {
        bigint id PK
        bigint user_id FK
        string ip_address "nullable"
        string user_agent "nullable"
        timestamp created_at
        timestamp updated_at
    }

    ACCESS_LOGS {
        bigint id PK
        bigint user_id FK
        string route_name "nullable"
        string url "nullable"
        string method "nullable"
        string ip_address "nullable"
        string user_agent "nullable"
        timestamp created_at
        timestamp updated_at
    }
```
## 7.セットアップ
# リポジトリのクローン
git clone https://github.com/KuboRaido/NEXUSPHERE.git
cd KuboRaido-NEXUSPHERE

# コンテナの起動
docker-compose up -d

# 依存パッケージのインストール (コンテナ内)
docker-compose exec php composer install
docker-compose exec php npm install
docker-compose exec php npm run build

# 環境設定・DBマイグレーション
docker-compose exec php cp .env.example .env
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan migrate --seed

## 8.今後の展望
* **PWA化** : モバイル端末でのネイティブアプリに近い操作性とプッシュ通知の導入。
* **検索エンジンの強化** : 形態素解析を用いた全文検索機能の追加。
* **CI/CD** : GitHub Actionsを用いた自動テスト・デプロイパイプラインの構築。