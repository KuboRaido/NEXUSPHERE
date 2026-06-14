# NEXUSPHERE - データベース設計書

**対象**: NEXUSPHEREプロジェクト（大学向けSNSプラットフォーム）  
**データベース**: MySQL 8.0+  
**ORM**: Laravel Eloquent  

---

## 📋 テーブル一覧

| # | テーブル名 | 説明 | レコード数目安 |
|---|-----------|------|-------------|
| 1 | **users** | ユーザー（学生）マスタ | 1,000+ |
| 2 | **profiles** | ユーザープロフィール | 1,000+ |
| 3 | **circles** | サークル・部活動 | 100+ |
| 4 | **circle_users** | サークルメンバー（中間テーブル） | 5,000+ |
| 5 | **circle_requests** | サークル参加リクエスト | 100+ |
| 6 | **groups** | グループ（サークル内） | 200+ |
| 7 | **groupmembers** | グループメンバー | 1,000+ |
| 8 | **prcs** | 投稿（Post Records） | 10,000+ |
| 9 | **nices** | いいね記録 | 50,000+ |
| 10 | **dms** | ダイレクトメッセージ | 100,000+ |
| 11 | **dm_reads** | DM既読管理 | 5,000+ |
| 12 | **images_and_videos** | 画像・動画ファイル | 20,000+ |

---

## 🗄️ テーブル詳細設計

### 1️⃣ users（ユーザーマスタ）

**用途**: 学生ユーザーの基本情報管理  
**タイプ**: マスタテーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **user_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | ユーザーID（主キー） |
| **mail** | VARCHAR(255) | UQ | ❌ | - | メールアドレス（ユニーク） |
| **password** | VARCHAR(255) | - | ❌ | - | パスワード（暗号化） |
| **name** | TEXT | - | ❌ | - | ユーザー名 |
| **age** | INT | - | ❌ | - | 年齢 |
| **grade** | INT | - | ❌ | - | 学年（1-4） |
| **subject** | TEXT | - | ❌ | - | 専攻科目 |
| **major** | TEXT | - | ❌ | - | 専攻分野 |
| **icon** | TEXT | - | ✅ | NULL | プロフィール画像URL |
| **job** | VARCHAR(255) | - | ✅ | NULL | 職務/役職 |
| **remember_token** | VARCHAR(100) | - | ✅ | NULL | Remember Me トークン |
| **email_verified_at** | TIMESTAMP | - | ✅ | NULL | メール認証日時 |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | レコード作成日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | レコード更新日 |

**インデックス:**
- PK: `user_id`
- UQ: `mail` （ログイン用）
- `email_verified_at` （メール認証ステータス確認）

**リレーション:**
```
users 1:N profiles
users 1:N prcs
users 1:N nices
users 1:N dms (sender_id)
users 1:N dms (receiver_id)
users 1:N circle_users
users 1:N circle_requests
users 1:N groupmembers
users 1:N dm_reads
```

---

### 2️⃣ profiles（ユーザープロフィール）

**用途**: ユーザーの詳細プロフィール情報  
**タイプ**: ユーザー拡張テーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **profile_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | プロフィールID |
| **user_id** | BIGINT UNSIGNED | FK | ❌ | - | ユーザーID（外部キー） |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 作成日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**制約:**
- FK: `user_id` → `users(user_id)` **ON DELETE CASCADE**

**リレーション:**
```
profiles N:1 users
profiles 1:N prcs
```

---

### 3️⃣ circles（サークル・部活動）

**用途**: 大学のサークルや部活動情報  
**タイプ**: マスタテーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **circle_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | サークルID |
| **category** | VARCHAR(255) | - | ✅ | NULL | カテゴリ（体育、文化など） |
| **circle_name** | VARCHAR(255) | UQ | ❌ | - | サークル名（ユニーク） |
| **owner_id** | INT | - | ❌ | - | オーナーユーザーID |
| **sentence** | VARCHAR(255) | - | ❌ | - | サークルの説明文 |
| **icon** | VARCHAR(255) | UQ | ✅ | NULL | サークルアイコン（ユニーク） |
| **members_count** | INT | - | ❌ | 0 | メンバー数（キャッシュ） |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 作成日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**インデックス:**
- PK: `circle_id`
- UQ: `circle_name` （サークル名の一意性）
- UQ: `icon` （アイコンの一意性）

**リレーション:**
```
circles 1:N circle_users
circles 1:N circle_requests
circles 1:N groups
circles 1:N prcs
circles 1:N dms
circles 1:N dm_reads
```

---

### 4️⃣ circle_users（サークルメンバー：中間テーブル）

**用途**: ユーザーとサークルの多対多関係  
**タイプ**: 中間テーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **circle_user_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | 主キー |
| **circle_id** | BIGINT UNSIGNED | FK | ❌ | - | サークルID（外部キー） |
| **user_id** | BIGINT UNSIGNED | FK | ✅ | NULL | ユーザーID（外部キー） |
| **role** | VARCHAR(50) | - | ✅ | NULL | ロール（owner, member など） |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 参加日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**制約:**
- FK: `circle_id` → `circles(circle_id)` **ON DELETE CASCADE**
- FK: `user_id` → `users(user_id)` **ON DELETE CASCADE**
- UQ: `(circle_id, user_id)` （同じサークルに同じユーザーは1回のみ）

**リレーション:**
```
circle_users N:1 circles
circle_users N:1 users
```

---

### 5️⃣ circle_requests（サークル参加リクエスト）

**用途**: サークル参加申請を管理  
**タイプ**: トランザクションテーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **circle_request_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | リクエストID |
| **user_id** | BIGINT UNSIGNED | FK | ❌ | - | ユーザーID（申請者） |
| **circle_id** | BIGINT UNSIGNED | FK | ❌ | - | サークルID |
| **status** | ENUM | - | ❌ | 'pending' | ステータス（pending/approved/rejected） |
| **request_at** | TIMESTAMP | - | ✅ | NULL | 申請日時 |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 作成日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**制約:**
- FK: `user_id` → `users(user_id)` **ON DELETE CASCADE**
- FK: `circle_id` → `circles(circle_id)` **ON DELETE CASCADE**

**リレーション:**
```
circle_requests N:1 users
circle_requests N:1 circles
```

---

### 6️⃣ groups（グループ：サークル内）

**用途**: サークル内のサブグループ管理  
**タイプ**: ツリー構造テーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **group_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | グループID |
| **group_name** | VARCHAR(255) | UQ | ❌ | - | グループ名 |
| **circle_id** | BIGINT UNSIGNED | FK | ✅ | NULL | 親サークルID |
| **icon** | TEXT | - | ✅ | NULL | グループアイコンURL |
| **members_count** | INT | - | ❌ | 0 | メンバー数（キャッシュ） |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 作成日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**制約:**
- FK: `circle_id` → `circles(circle_id)` **ON DELETE CASCADE**
- UQ: `group_name`

**リレーション:**
```
groups N:1 circles
groups 1:N groupmembers
groups 1:N dms
groups 1:N dm_reads
```

---

### 7️⃣ groupmembers（グループメンバー）

**用途**: グループとユーザーの関連付け  
**タイプ**: 中間テーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **groupmember_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | 主キー |
| **user_id** | BIGINT UNSIGNED | - | ❌ | - | ユーザーID |
| **group_id** | BIGINT UNSIGNED | - | ❌ | - | グループID |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 参加日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**インデックス:**
- UQ: `(user_id, group_id)` （重複防止）
- IDX: `group_id` （グループからメンバー取得用）

**リレーション:**
```
groupmembers N:1 users
groupmembers N:1 groups
```

---

### 8️⃣ prcs（投稿：Post Records）

**用途**: ユーザーの投稿（テキスト、コメント、返信）  
**タイプ**: トランザクションテーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **prc_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | 投稿ID |
| **user_id** | BIGINT UNSIGNED | FK | ✅ | NULL | 投稿ユーザー |
| **sentence** | TEXT | - | ✅ | NULL | 投稿内容 |
| **profile_id** | BIGINT UNSIGNED | FK | ✅ | NULL | プロフィール参照 |
| **type** | INT | - | ✅ | NULL | 投稿タイプ（0=通常, 1=コメント, 3=サークル投稿） |
| **parent_id** | INT | - | ✅ | NULL | 親投稿ID（返信元） |
| **circle_id** | BIGINT UNSIGNED | FK | ✅ | NULL | サークルID（サークル投稿の場合） |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 作成日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**制約:**
- FK: `user_id` → `users(user_id)` **ON DELETE CASCADE**
- FK: `profile_id` → `profiles(profile_id)` **ON DELETE CASCADE**
- FK: `circle_id` → `circles(circle_id)` **ON DELETE CASCADE**

**インデックス:**
- PK: `prc_id`
- FK: `user_id`, `profile_id`, `circle_id`

**リレーション:**
```
prcs N:1 users
prcs N:1 profiles
prcs N:1 circles
prcs 1:N nices
prcs 1:N images_and_videos
```

---

### 9️⃣ nices（いいね）

**用途**: 投稿へのいいね記録  
**タイプ**: トランザクションテーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **nice_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | いいねID |
| **prc_id** | INT | - | ❌ | - | 投稿ID |
| **user_id** | INT | - | ❌ | - | ユーザーID（いいねした人） |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | いいね日時 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**インデックス:**
- PK: `nice_id`
- UQ: `(prc_id, user_id)` （同じユーザーが同じ投稿に複数回いいねできない） ⚠️ **改善候補**

**リレーション:**
```
nices N:1 prcs
nices N:1 users
```

---

### 🔟 dms（ダイレクトメッセージ）

**用途**: ユーザー間、サークル、グループのメッセージ  
**タイプ**: トランザクションテーブル（最も複雑）  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **dm_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | メッセージID |
| **circle_id** | BIGINT UNSIGNED | FK | ✅ | NULL | サークルID（サークルメッセージ） |
| **user_id** | BIGINT UNSIGNED | FK | ❌ | - | ユーザーID（所属） |
| **group_id** | BIGINT UNSIGNED | FK | ✅ | NULL | グループID（グループメッセージ） |
| **sender_id** | BIGINT UNSIGNED | FK | ❌ | - | 送信者ID |
| **receiver_id** | BIGINT UNSIGNED | FK | ✅ | NULL | 受信者ID（1対1メッセージ） |
| **message_text** | TEXT | - | ✅ | NULL | メッセージ本文 |
| **conversation_id** | BIGINT UNSIGNED | IDX | ✅ | NULL | 会話ID（スレッド管理） |
| **attachments** | JSON | - | ✅ | NULL | 添付ファイル情報 |
| **dm_key** | VARCHAR(255) | - | ✅ | NULL | メッセージキー（重複排除） |
| **parent_id** | BIGINT UNSIGNED | FK | ✅ | NULL | 親メッセージID（返信元） |
| **reply_to_dm_id** | BIGINT UNSIGNED | FK | ✅ | NULL | 返信先メッセージID |
| **deleted_at** | TIMESTAMP | - | ✅ | NULL | ソフトデリート日 |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 送信日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**制約:**
- FK: `circle_id` → `circles(circle_id)` **ON DELETE CASCADE**
- FK: `user_id` → `users(user_id)` **ON DELETE CASCADE**
- FK: `group_id` → `groups(group_id)` **ON DELETE CASCADE**
- FK: `sender_id` → `users(user_id)` **ON DELETE CASCADE**
- FK: `receiver_id` → `users(user_id)` **ON DELETE CASCADE**
- FK: `parent_id` → `dms(dm_id)` **ON DELETE SET NULL**
- FK: `reply_to_dm_id` → `dms(dm_id)` **ON DELETE SET NULL**

**インデックス（パフォーマンス最適化）:**
```sql
INDEX `dm_circle_created_idx` (circle_id, created_at)
INDEX `dm_user_created_idx` (user_id, created_at)
INDEX `dm_group_created_idx` (group_id, created_at)
INDEX `dm_sender_created_idx` (sender_id, created_at)
INDEX `dm_receiver_created_idx` (receiver_id, created_at)
INDEX `dm_dmkey_created_idx` (dm_key, created_at)
INDEX (sender_id, receiver_id, created_at)
INDEX (receiver_id, sender_id, created_at)
INDEX (conversation_id)
```

**リレーション:**
```
dms N:1 users (user_id)
dms N:1 users (sender_id)
dms N:1 users (receiver_id)
dms N:1 circles
dms N:1 groups
dms 1:N images_and_videos
```

---

### 1️⃣1️⃣ dm_reads（DM既読管理）

**用途**: ユーザーのDM既読状態を追跡  
**タイプ**: ステート管理テーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | 主キー |
| **user_id** | BIGINT UNSIGNED | FK | ❌ | - | ユーザーID（既読者） |
| **partner_id** | BIGINT UNSIGNED | FK | ✅ | NULL | 相手ユーザーID（1対1メッセージ） |
| **circle_id** | BIGINT UNSIGNED | FK | ✅ | NULL | サークルID |
| **group_id** | BIGINT UNSIGNED | FK | ✅ | NULL | グループID |
| **last_read_at** | TIMESTAMP | - | ✅ | NULL | 最終既読日時 |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 作成日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**制約:**
- FK: `user_id` → `users(user_id)` **ON DELETE CASCADE**
- FK: `partner_id` → `users(user_id)` **ON DELETE CASCADE**
- FK: `circle_id` → `circles(circle_id)` **ON DELETE CASCADE**
- FK: `group_id` → `groups(group_id)` **ON DELETE CASCADE**
- UQ: `(user_id, partner_id)` ← **upsert の衝突キー**

**インデックス:**
- UQ: `(user_id, partner_id)` （1対1メッセージの既読管理）
- IDX: `(partner_id, user_id)` （相手から見た既読確認）

**リレーション:**
```
dm_reads N:1 users (user_id)
dm_reads N:1 users (partner_id)
dm_reads N:1 circles
dm_reads N:1 groups
```

---

### 1️⃣2️⃣ images_and_videos（メディアファイル）

**用途**: 投稿やメッセージの画像・動画  
**タイプ**: ファイルメタデータテーブル  

| カラム | 型 | キー | NULL可 | デフォルト | 説明 |
|-------|-----|------|--------|-----------|------|
| **image_and_video_id** | BIGINT UNSIGNED | PK | ❌ | Auto Increment | ファイルID |
| **prc_id** | BIGINT UNSIGNED | FK | ✅ | NULL | 投稿ID（投稿添付の場合） |
| **video** | TEXT | - | ✅ | NULL | 動画ファイルURL |
| **image** | TEXT | - | ✅ | NULL | 画像ファイルURL |
| **dm_id** | BIGINT UNSIGNED | FK | ✅ | NULL | メッセージID（DM添付） |
| **created_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 作成日 |
| **updated_at** | TIMESTAMP | - | ❌ | CURRENT_TIMESTAMP | 更新日 |

**制約:**
- FK: `prc_id` → `prcs(prc_id)` **ON DELETE CASCADE**
- FK: `dm_id` → `dms(dm_id)` **ON DELETE CASCADE**

**インデックス:**
- IDX: `(prc_id, dm_id)` （投稿またはDMから画像検索）

**リレーション:**
```
images_and_videos N:1 prcs
images_and_videos N:1 dms
```

---

## 📊 ER図（エンティティ・リレーションシップ図）

```
users (学生ユーザー)
├─ 1:1 profiles
├─ 1:N prcs
├─ 1:N nices
├─ 1:N circle_users
├─ 1:N circle_requests
├─ 1:N groupmembers
├─ 1:N dms (sender)
├─ 1:N dms (receiver)
└─ 1:N dm_reads

circles (サークル)
├─ 1:N circle_users
├─ 1:N circle_requests
├─ 1:N groups
├─ 1:N prcs
├─ 1:N dms
└─ 1:N dm_reads

groups (グループ)
├─ N:1 circles
├─ 1:N groupmembers
├─ 1:N dms
└─ 1:N dm_reads

prcs (投稿)
├─ N:1 users
├─ N:1 profiles
├─ N:1 circles
├─ 1:N nices
└─ 1:N images_and_videos

dms (メッセージ)
├─ N:1 circles
├─ N:1 groups
├─ N:1 users
├─ N:1 senders (users.sender_id)
├─ N:1 receivers (users.receiver_id)
└─ 1:N images_and_videos

images_and_videos (メディア)
├─ N:1 prcs
└─ N:1 dms
```

---

## 🔑 キー戦略

### 主キー（Primary Key）

| テーブル | PK カラム | 型 | 説明 |
|---------|-----------|-----|------|
| users | user_id | BIGINT UNSIGNED | 自動採番 |
| profiles | profile_id | BIGINT UNSIGNED | 自動採番 |
| circles | circle_id | BIGINT UNSIGNED | 自動採番 |
| circle_users | circle_user_id | BIGINT UNSIGNED | 自動採番 |
| circle_requests | circle_request_id | BIGINT UNSIGNED | 自動採番 |
| groups | group_id | BIGINT UNSIGNED | 自動採番 |
| groupmembers | groupmember_id | BIGINT UNSIGNED | 自動採番 |
| prcs | prc_id | BIGINT UNSIGNED | 自動採番 |
| nices | nice_id | BIGINT UNSIGNED | 自動採番 |
| dms | dm_id | BIGINT UNSIGNED | 自動採番 |
| dm_reads | id | BIGINT UNSIGNED | 自動採番 |
| images_and_videos | image_and_video_id | BIGINT UNSIGNED | 自動採番 |

### ユニークキー（Unique Key）

| テーブル | カラム | 説明 |
|---------|--------|------|
| users | mail | ログイン時のメール一意性 |
| circles | circle_name | サークル名の一意性 |
| circles | icon | アイコンの一意性 |
| circle_users | (circle_id, user_id) | サークル内での重複メンバー防止 |
| groups | group_name | グループ名の一意性 |
| groupmembers | (user_id, group_id) | グループ内での重複メンバー防止 |
| nices | (prc_id, user_id) | ⚠️ 同じ投稿への複数いいね防止 |
| dm_reads | (user_id, partner_id) | upsert の衝突キー |

---

## 🚀 インデックス戦略

### パフォーマンス最適化インデックス

```sql
-- users テーブル
CREATE INDEX idx_users_email ON users(mail);
CREATE INDEX idx_users_created_at ON users(created_at DESC);

-- circles テーブル
CREATE INDEX idx_circles_owner_id ON circles(owner_id);
CREATE INDEX idx_circles_created_at ON circles(created_at DESC);

-- circle_users テーブル
CREATE INDEX idx_circle_users_user_id ON circle_users(user_id);
CREATE INDEX idx_circle_users_circle_id ON circle_users(circle_id);

-- prcs テーブル
CREATE INDEX idx_prcs_user_id ON prcs(user_id);
CREATE INDEX idx_prcs_circle_id ON prcs(circle_id);
CREATE INDEX idx_prcs_type_created ON prcs(type, created_at DESC);
CREATE INDEX idx_prcs_parent_id ON prcs(parent_id);

-- nices テーブル
CREATE INDEX idx_nices_prc_id ON nices(prc_id);
CREATE INDEX idx_nices_user_id ON nices(user_id);

-- dms テーブル（複雑）
CREATE INDEX idx_dms_circle_created ON dms(circle_id, created_at DESC);
CREATE INDEX idx_dms_group_created ON dms(group_id, created_at DESC);
CREATE INDEX idx_dms_sender_created ON dms(sender_id, created_at DESC);
CREATE INDEX idx_dms_receiver_created ON dms(receiver_id, created_at DESC);
CREATE INDEX idx_dms_conversation_id ON dms(conversation_id);
CREATE INDEX idx_dms_dm_key ON dms(dm_key);

-- dm_reads テーブル
CREATE INDEX idx_dm_reads_user_partner ON dm_reads(user_id, partner_id);
CREATE INDEX idx_dm_reads_partner_user ON dm_reads(partner_id, user_id);
```

---

## 🔒 制約（Constraints）

### 外部キー制約（Foreign Key）

**一般的なルール:**
- **ON DELETE CASCADE**: 親テーブルの削除時に子レコードも削除
- **ON DELETE SET NULL**: 親テーブルの削除時に外部キーをNULLに設定
- **ON DELETE RESTRICT**: 親テーブルの削除を禁止

**適用パターン:**

```
ON DELETE CASCADE:
├─ users → profiles, prcs, nices, circle_users, circle_requests, groupmembers, dms
├─ circles → circle_users, circle_requests, groups, prcs, dms, dm_reads
├─ groups → groupmembers, dms, dm_reads
└─ prcs → nices, images_and_videos

ON DELETE SET NULL:
└─ dms.parent_id, dms.reply_to_dm_id → dms (返信チェーンの保持)
```

---

## 📈 データ特性と最適化

### テーブルサイズ目安

| テーブル | 年単位 | 月単位 |
|---------|--------|--------|
| users | 1,000-10,000 | 50-200 |
| prcs | 50,000-500,000 | 4,000-40,000 |
| dms | 100,000-1,000,000 | 10,000-100,000 |
| nices | 100,000-1,000,000 | 10,000-100,000 |
| images_and_videos | 20,000-200,000 | 2,000-20,000 |

### ホットテーブル（頻繁にアクセス）

1. **dms** - メッセージ送受信、既読管理
2. **prcs** - 投稿の表示、検索
3. **nices** - いいね機能
4. **dm_reads** - 既読状態確認

### 最適化テクニック

#### 1. キャッシング
```php
// circle_users のメンバーをキャッシュ
Cache::rememberForever('circle_' . $circleId . '_members', function() {
    return Circle::find($circleId)->users;
});
```

#### 2. デノーマライズ（キャッシュカラム）
```
circles.members_count ← circle_users の集計
groups.members_count ← groupmembers の集計
```

#### 3. Eager Loading
```php
// N+1 クエリ防止
prcs::with(['user', 'comments', 'images'])->get();

dms::with(['sender', 'receiver', 'images'])->get();
```

#### 4. ページネーション
```php
// 大量レコード取得時
prcs::paginate(20);
dms::paginate(50);
```

---

## 🐛 既知の問題と改善案

### Issue 1: Eager Loading バグ（修正済み）
**問題**: `.get(['cols'])` カラム指定で関連データが読み込まれない
```php
// Before（NG）
$dms = Dm::with('images_and_videos')->get(['id', 'content']);

// After（OK）
$dms = Dm::with('images_and_videos')->get();
```

### Issue 2: nice テーブルの外部キー欠落
**問題**: `nices` テーブルに外部キー制約がない
```php
// 改善案
ALTER TABLE nices ADD CONSTRAINT fk_nices_prc_id 
FOREIGN KEY (prc_id) REFERENCES prcs(prc_id) ON DELETE CASCADE;
ALTER TABLE nices ADD CONSTRAINT fk_nices_user_id 
FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
```

### Issue 3: DM テーブルの複雑性
**問題**: circle_id, user_id, group_id, sender_id, receiver_id が混在して複雑
**改善案**: メッセージタイプを分離（直接メッセージテーブルとグループメッセージテーブル）

### Issue 4: dm_key フィールドの未使用
**問題**: メッセージ重複排除用の dm_key が実装されていない
**改善案**: メッセージ送信時に `md5(sender_id + receiver_id + timestamp)` で生成

---

## 💾 バックアップ・リカバリ戦略

### バックアップ対象の優先度

**High Priority（毎日）:**
- users, circle_users, groupmembers
- prcs, dms, nices
- dm_reads, circle_requests

**Medium Priority（週単位）:**
- profiles, circles, groups
- images_and_videos（ファイルは別途S3で管理）

**Low Priority（月単位）:**
- キャッシュテーブル

---

## 🔐 セキュリティ考慮

### SQL インジェクション対策
- ✅ Eloquent ORM を使用（バインドパラメータ）
- ✅ FormRequest でバリデーション

### データ保護
- ✅ パスワード: bcrypt ハッシュ
- ✅ メールアドレス: GDPR 対応削除機能
- ✅ ソフトデリート: `deleted_at` で論理削除

### アクセス制御
- ✅ Policy で行レベルアクセス制御
- ✅ 認可ミドルウェア

---

## 📋 チェックリスト

### DB 設計確認
- [x] 正規化（3NF）
- [x] 外部キー制約
- [x] インデックス最適化
- [x] ユニーク制約
- [x] NULL 許容性

### パフォーマンス
- [x] Eager Loading
- [x] インデックス戦略
- [x] ページネーション
- [x] キャッシング

### セキュリティ
- [x] SQL インジェクション対策
- [x] データ暗号化
- [x] ソフトデリート

---

**作成日**: 2026年6月12日  
**対象**: NEXUSPHERE（大学向けSNSプラットフォーム）  
**DB**: MySQL 8.0+ / Laravel Eloquent
