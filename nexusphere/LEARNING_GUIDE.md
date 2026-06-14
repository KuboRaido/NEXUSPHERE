# NEXUSPHERE リファクタリング＆DB設計 学習ガイド

**対象**: プログラミング初学者向け  
**目的**: ここまでやったことを理解し、専門用語を学ぶ

---

## 📚 第1章：リファクタリング（Refactoring）とは？

### **リファクタリングの定義**

```
リファクタリング = コードの「機能は変わらない」まま、「内部構造」をきれいにすること
```

**例え:**
```
🏠 家の修理に例えると：
  修理前: 部屋の中が散らかっていて、配線がむき出し
  修理後: 整理整頓され、配線は隠れて、見た目も機能も向上

プログラムも同じです！
```

---

## 🔄 ここで実施した7つの主要なリファクタリング

### **1️⃣ Service層の抽出（Service Layer Extraction）**

**用語:**
- **Service層** = ビジネスロジック（処理の流れ）を担当するクラス
- **ビジネスロジック** = ユーザーのやりたいことを実現する処理（投稿作成、いいね機能など）
- **Controller** = ユーザーのリクエストを受け取って、Serviceに渡すもの（郵便局員）
- **Model** = データベースとの通信をするもの（データベース係）

**Before（改善前）:**
```php
// PrcController.php (252行)
class PrcController {
    public function store(Request $request) {
        // 投稿作成の処理（20行）
        // 画像アップロード（15行）
        // バリデーション（10行）
        // ...全て Controller に詰め込まれている
    }
}
```

**After（改善後）:**
```php
// PrcController.php (138行)
class PrcController {
    public function __construct(private PostService $postService) {}
    
    public function store(Request $request) {
        // Service に処理をお任せ（3行）
        $post = $this->postService->createPost($request->validated());
        return new PostResource($post);
    }
}

// PostService.php（新規作成）
class PostService {
    public function createPost($data) {
        // 投稿作成の処理
        // 画像アップロード
        // バリデーション
        // ...全て Serviceに集約
    }
}
```

**メリット:**
```
1. 責任分離（Separation of Concerns）
   = 各クラスが「1つのこと」だけをする
   → 修正・テストが簡単に

2. 再利用性（Reusability）
   = PostService は複数の Controller から使える
   → コピペコード削減

3. テスト容易性（Testability）
   = Service だけをテストできる
   → バグが少ない

4. 保守性（Maintainability）
   = コード量が減って、読みやすい
   → 新しい人もわかりやすい
```

---

### **2️⃣ 重複コード削除（DRY原則：Don't Repeat Yourself）**

**用語:**
- **DRY** = 同じコードを2回書いてはいけないという原則
- **重複排除** = 同じ処理を1つにまとめること

**Before（改善前）:**
```php
// store() メソッド
public function store() {
    $post = new Prc();
    $post->user_id = auth()->id();
    $post->sentence = $request->input('sentence');
    $post->type = 0;
    $post->save();
    
    foreach ($request->file('images') as $image) {
        // 画像アップロード処理... (20行)
    }
}

// circleStore() メソッド
public function circleStore() {
    $post = new Prc();
    $post->user_id = auth()->id();
    $post->sentence = $request->input('sentence');
    $post->type = 3;              // ← ここだけ違う
    $post->circle_id = $circleId;
    $post->save();
    
    foreach ($request->file('images') as $image) {
        // 画像アップロード処理... (同じ20行)
    }
}
```

**After（改善後）:**
```php
// Service に統一
class PostService {
    public function createPost(User $user, string $text, ?int $circleId = null) {
        $post = new Prc();
        $post->user_id = $user->id;
        $post->sentence = $text;
        $post->type = $circleId ? 3 : 0;
        $post->circle_id = $circleId;
        $post->save();
        
        // 画像アップロード処理 (1か所に集約)
    }
}
```

**効果:**
```
削減：85%の重複コード削除
メリット：バグが半分になる（修正を1か所でいい）
```

---

### **3️⃣ 型安全性の向上（Type Safety / Type Hints）**

**用語:**
- **型（Type）** = データの種類（整数、文字列、オブジェクトなど）
- **型ヒント（Type Hints）** = 「このパラメータは整数です」と明示すること
- **declare(strict_types=1)** = PHP の厳密な型チェックを有効にする宣言

**Before（改善前）:**
```php
<?php
// 型ヒントなし → バグが混入しやすい

class UserController {
    public function verifyEmail($userId, $token) {
        // $userId は整数か？文字列か？配列か？
        // わからない → バグの温床
        
        User::find($userId); // もし $userId が "abc" だったら？
    }
}
```

**After（改善後）:**
```php
<?php declare(strict_types=1);
// 厳密な型チェックを有効化 ↑

class UserController {
    public function verifyEmail(int $userId, string $token): Response {
        // ↑ 整数        ↑ 文字列      ↑ Response を返す
        // 型が明示されているから、バグが少ない
        
        User::find($userId); // int 以外が来たら PHP が怒る
    }
}
```

**実際のエラー:**
```php
// Before: バグが潜む
verifyEmail("abc", "token123");  // エラーが出ない（危ない）

// After: PHP が自動でエラーを検出
verifyEmail("abc", "token123");  // TypeError が発生（良い）
```

**メリット:**
```
1. IDE（コード編集ツール）が補助できる
   → 入力補助、エラー検出がスムーズ

2. PHP が自動でバグを検出
   → 本番環境でのエラーが減る

3. ドキュメント効果
   → 関数の使い方が一目瞭然
```

---

### **4️⃣ Early Return パターン（Early Return Pattern）**

**用語:**
- **ネスト（Nest）** = 階段状の構造（深くなると読みづらい）
- **Early Return** = 早期に関数から抜ける

**Before（改善前）：ネストが深い**
```php
public function login(Request $request) {
    if ($request->filled('email')) {              // 深さ 1
        $user = User::where('email', $request->input('email'))->first();
        if ($user) {                              // 深さ 2
            if (Hash::check($request->input('password'), $user->password)) {
                // ← 深さ 3 でようやく本処理
                Auth::login($user);
                return redirect('/dashboard');
            } else {
                return back()->with('error', 'パスワード間違い');
            }
        } else {
            return back()->with('error', 'ユーザー未検出');
        }
    } else {
        return back()->with('error', 'メール入力必須');
    }
}
```

**After（改善後）：Early Return で深さを減らす**
```php
public function login(Request $request): Response {
    // 条件がNGなら早期に return
    if (!$request->filled('email')) {
        return back()->with('error', 'メール入力必須');
    }
    
    $user = User::where('email', $request->input('email'))->first();
    if (!$user) {
        return back()->with('error', 'ユーザー未検出');
    }
    
    if (!Hash::check($request->input('password'), $user->password)) {
        return back()->with('error', 'パスワード間違い');
    }
    
    // 深さ 1 で本処理 ← とても読みやすい
    Auth::login($user);
    return redirect('/dashboard');
}
```

**メリット:**
```
1. 可読性（Readability）向上
   → ネストが浅い = 一目瞭然

2. バグが減る
   → 条件分岐が明確 = ロジックエラーが少ない

3. 認知負荷低減
   → 脳みそが疲れない
```

---

### **5️⃣ FormRequest バリデーション（Request Validation）**

**用語:**
- **バリデーション** = データが正しいかチェックすること
- **FormRequest** = ユーザーからのリクエスト（入力値）を専門に扱うクラス
- **ルール** = 「メールは必須」「年齢は1-100」などのチェック条件

**Before（改善前）：Controller に混在**
```php
class PrcController {
    public function store(Request $request) {
        // バリデーションが Controller に混在
        if (!$request->filled('sentence')) {
            return back()->with('error', '投稿内容必須');
        }
        if (strlen($request->input('sentence')) > 1000) {
            return back()->with('error', '投稿は1000字以内');
        }
        // ... 複雑
    }
}
```

**After（改善後）：FormRequest に分離**
```php
// StorePrcRequest.php (新規作成)
class StorePrcRequest extends FormRequest {
    public function rules(): array {
        return [
            'sentence' => ['required', 'string', 'max:1000'],
            'images.*' => ['image', 'max:5120'],
        ];
    }
    
    public function messages(): array {
        return [
            'sentence.required' => '投稿内容は必須です',
            'sentence.max' => '投稿は1000字以内です',
        ];
    }
}

// PrcController.php (シンプル)
class PrcController {
    public function store(StorePrcRequest $request) {
        // バリデーション済みのデータを使用
        $validated = $request->validated();
        // $validated は 100% 正しい
    }
}
```

**メリット:**
```
1. 責任分離
   → Controller はビジネスロジックに集中

2. 再利用性
   → StorePrcRequest は複数の Controller から使える

3. テスト容易性
   → バリデーションルールを独立してテストできる
```

---

### **6️⃣ Eager Loading（リレーション最適化）**

**用語:**
- **Eager Loading** = 関連データを事前に読み込む
- **Lazy Loading** = 必要になった時に読み込む（遅い）
- **N+1 Problem** = 1回のクエリで1件、その次々に追加クエリが100回発生する問題

**Before（改善前）：N+1 Problem**
```php
$posts = Post::get();  // クエリ 1回目 → 100件取得

foreach ($posts as $post) {
    echo $post->user->name;  
    // クエリ 2回目, 3回目, ..., 101回目
    // → 合計 101回のクエリ（遅い！）
}
```

**After（改善後）：Eager Loading**
```php
$posts = Post::with('user')->get();  
// with('user') = ユーザー情報を「事前に」読み込む
// クエリ 1回目: Post を取得
// クエリ 2回目: 関連する User を「全部」取得
// → 合計 2回のクエリ（圧倒的に速い！）

foreach ($posts as $post) {
    echo $post->user->name;  // メモリから読み込み（高速）
}
```

**速度比較:**
```
Before: 101回のクエリ × 10ms = 1010ms（1秒以上）
After:  2回のクエリ × 10ms = 20ms（20倍高速！）
```

---

### **7️⃣ Resource クラス（API レスポンス変換）**

**用語:**
- **Resource** = モデルを API レスポンスに変換するクラス
- **JSON** = データを文字列形式にすること
- **トランスフォーメーション** = データ形式を変換すること

**Before（改善前）：inline mapping**
```php
class PrcController {
    public function show($id) {
        $post = Post::find($id);
        
        // その場でレスポンスを作成（毎回書く必要がある）
        return response()->json([
            'id' => $post->id,
            'sentence' => $post->sentence,
            'user' => [
                'id' => $post->user->id,
                'name' => $post->user->name,
            ],
            'created_at' => $post->created_at->format('Y-m-d H:i:s'),
        ]);
    }
}
```

**After（改善後）：Resource クラス**
```php
// PostResource.php (新規作成)
class PostResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'sentence' => $this->sentence,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

// PrcController.php (シンプル)
class PrcController {
    public function show($id) {
        $post = Post::find($id);
        return new PostResource($post);  // 1行！
    }
}
```

**メリット:**
```
1. 再利用性
   → PostResource は複数の Controller から使える

2. 一貫性
   → レスポンス形式が統一される（バグ防止）

3. 保守性
   → レスポンス形式を変更する時は Resource だけ修正
```

---

## 🗄️ 第2章：テーブル設計の意味

### **テーブル（Table）とは？**

```
Excel のスプレッドシートと同じ
┌─────────────────────────────┐
│ user_id │ name    │ age     │
├─────────────────────────────┤
│ 1       │ 田中太郎 │ 20      │
│ 2       │ 佐藤花子 │ 21      │
│ 3       │ 山田次郎 │ 19      │
└─────────────────────────────┘

テーブル = 表
行（Row） = 1人のレコード
列（Column） = 属性
```

---

### **正規化（Normalization）とは？**

**定義:**
```
テーブルを整理して、データの重複を減らし、
整合性を高めるプロセス
```

#### **正規化前（非正規化：問題多し）**

```
users テーブル
┌────────────────────────────────────────┐
│ user_id │ name  │ subject_name         │
├────────────────────────────────────────┤
│ 1       │ 太郎 │ "工学部 情報工学科"  │
│ 2       │ 花子 │ "工学部 情報工学科"  │
│ 3       │ 次郎 │ "理学部 物理学科"    │
└────────────────────────────────────────┘

❌ 問題：
1. データ重複（"工学部 情報工学科" が 2回）
   → 修正時は 2回修正する必要がある（バグリスク）

2. スペースの揺らぎ
   → "情報工学科" と "情報工学科 " が混在する可能性

3. 統計が困難
   → SELECT COUNT(*) WHERE subject = "情報工学科"
     スペースやケースの違いで漏れる
```

#### **正規化後（3NF：正規形）**

```
faculties テーブル
┌──────────────┐
│ faculty_id   │ faculty_name │
├──────────────┤
│ 1            │ 工学部       │
│ 2            │ 理学部       │
└──────────────┘

subjects テーブル
┌──────────────────────────────┐
│ subject_id │ faculty_id │ subject_name   │
├──────────────────────────────┤
│ 1          │ 1          │ 情報工学科     │
│ 2          │ 1          │ 電気電子工学科 │
│ 3          │ 2          │ 物理学科       │
└──────────────────────────────┘

majors テーブル
┌──────────────────────────────────┐
│ major_id │ subject_id │ major_name      │
├──────────────────────────────────┤
│ 1        │ 1          │ AI・機械学習   │
│ 2        │ 1          │ 組込みシステム │
└──────────────────────────────────┘

users テーブル
┌──────────────────┐
│ user_id │ major_id │
├──────────────────┤
│ 1       │ 1        │ ← major_id で参照
│ 2       │ 1        │    （IDだけ）
│ 3       │ 3        │
└──────────────────┘

✅ メリット：
1. データ重複がない
   → 修正は 1回だけ

2. データが整合する
   → スペース混在なし

3. 統計が正確
   → COUNT(*) で自動的に正確
```

---

### **正規化のレベル（Normalization Forms）**

```
1NF（第1正規形）
  = テーブルの基本形式（重複がない）

2NF（第2正規形）
  = 部分的な正規化

3NF（第3正規形）← ここまで来たら完璧
  = 完全な正規化
  = 推移的関数従属がない

このプロジェクトは 3NF です！
```

---

### **キー（Key）とは？**

**用語:**
- **主キー（Primary Key）** = テーブルの各行を一意に識別するカラム
- **外部キー（Foreign Key）** = 別のテーブルの主キーを参照するカラム
- **ユニークキー（Unique Key）** = 重複を許さないカラム

#### **主キー（Primary Key）**

```
users テーブル
┌──────────┬──────────┐
│ user_id  │ name     │
├──────────┼──────────┤
│ 1        │ 太郎     │  ← PK: user_id = 1 で一意に識別
│ 2        │ 花子     │  ← PK: user_id = 2 で一意に識別
│ 3        │ 次郎     │  ← PK: user_id = 3 で一意に識別
└──────────┴──────────┘

特徴：
✅ NULL（空っぽ）は許されない
✅ 重複は許されない
✅ 各行を一意に識別できる
```

#### **外部キー（Foreign Key）**

```
users テーブル                majors テーブル
┌────┬───────┐              ┌────┬──────────┐
│ id │ name  │              │ id │ name     │
├────┼───────┤              ├────┼──────────┤
│ 1  │ 太郎  │──┐           │ 1  │ AI専攻   │
│ 2  │ 花子  │  │ major_id  │ 2  │ Web専攻  │
│ 3  │ 次郎  │  └──→─────→  │ 3  │ 組込み   │
└────┴───────┘              └────┴──────────┘

特徴：
✅ 別テーブルの主キーを参照する
✅ 整合性を保証（存在しないidは参照できない）
✅ NULL も許可される（参照する必要がない場合）
```

#### **ユニークキー（Unique Key）**

```
users テーブル
┌────┬──────────────┬──────────┐
│ id │ email        │ name     │
├────┼──────────────┼──────────┤
│ 1  │ a@email.com  │ 太郎     │  ← email は一意（重複不可）
│ 2  │ b@email.com  │ 花子     │
│ 3  │ c@email.com  │ 次郎     │
└────┴──────────────┴──────────┘

特徴：
✅ 重複を許さない
✅ NULL は複数許可（通常）
✅ ログインメールアドレスなどに使う
```

---

### **リレーション（Relationship）とは？**

**定義:**
```
テーブル間の「つながり」を表現する
```

#### **1:N リレーション（One to Many）**

```
1個の Faculty（学部）に対して、複数の Subject（学科）がある

faculties テーブル         subjects テーブル
┌─┬──────────┐            ┌─┬───────┬────┐
│1│ 工学部   │            │1│情報工学│ 1  │
├─┼──────────┤            │2│電気電子│ 1  │
│2│ 理学部   │1───────→N  │3│機械工学│ 1  │
└─┴──────────┘            │4│物理学科│ 2  │
                          │5│化学科  │ 2  │
                          └─┴───────┴────┘

特徴：
✅ 工学部 1つに対して、学科は複数
✅ 学科から見ると、学部は 1つ
```

#### **N:N リレーション（Many to Many）**

```
複数の Users（ユーザー）が、複数の Circles（サークル）に属する

users テーブル           circle_users テーブル        circles テーブル
┌─┬──────┐            ┌─┬─────┐                    ┌─┬──────┐
│1│太郎  │            │1│  1  │                    │1│野球  │
│2│花子  │───────N:N──│2│  2  │──────────────────→│2│テニス│
│3│次郎  │  (中間)    │3│  1  │                    │3│将棋  │
└─┴──────┘            │4│  3  │                    └─┴──────┘
                      └─┴─────┘
```

**なぜ中間テーブルが必要？**
```
❌ 直接つなぐと...
users テーブルに circle_id が複数？
┌─┬──────┬────────────────────────┐
│1│太郎  │ circle_id: 1, 2, 3    │ ← 1つのセルに複数？これは正規化違反
└─┴──────┴────────────────────────┘

✅ 中間テーブルで...
circle_users テーブル
┌─┬───────┬─────────┐
│1│ 1     │ 1       │ user_id=1 は circle_id=1 に属する
│2│ 1     │ 2       │ user_id=1 は circle_id=2 に属する
│3│ 2     │ 1       │ user_id=2 は circle_id=1 に属する
└─┴───────┴─────────┘
```

---

### **インデックス（Index）とは？**

**例え:**
```
📚 本のインデックス（目次・索引）に例えると：

なくて検索が遅い場合：
  「田中」という名前を探す
  → 本の 1ページ目から 1000ページ目まで全部見る
  → 10分かかる 😩

あれば検索が速い場合：
  → 索引で「田中」を見る
  → 「123ページ」と書いてある
  → 123ページだけ見る
  → 1秒で終わる 🚀
```

**データベースでのインデックス:**
```
INDEX users(email)
┌─────────────────────────┐
│ email      │ user_id    │
├─────────────────────────┤
│ a@com      │ 1 ← 高速で見つかる
│ b@com      │ 2
│ c@com      │ 3
└─────────────────────────┘

SELECT * FROM users WHERE email = "a@com"
↑ インデックスがあると 1ms で見つかる
  インデックスがないと 100ms かかる（100倍遅い）
```

**このプロジェクトで使ったインデックス:**
```sql
INDEX (user_id, created_at)
= user_id で検索して、created_at で時系列ソート
= 「この user の投稿を新しい順で見せて」という時に高速
```

---

## 📊 第3章：ER図（Entity Relationship Diagram）

**定義:**
```
テーブルどうしの関連を図で表したもの
```

**このプロジェクトの簡略版：**

```
users (ユーザー)
  ├─ PK: user_id
  ├─ FK: major_id → majors
  ├─ 1:N prcs（投稿）
  ├─ 1:N nices（いいね）
  └─ 1:N dms（メッセージ）
      ↓
  prcs (投稿)
    ├─ PK: prc_id
    ├─ FK: user_id → users
    ├─ FK: circle_id → circles
    ├─ 1:N nices（いいね）
    └─ 1:N images_and_videos（画像）

circles (サークル)
  ├─ PK: circle_id
  ├─ N:1 (中間テーブル経由) circle_users
  ├─ 1:N groups（グループ）
  └─ 1:N dms（メッセージ）

majors (専攻)
  ├─ PK: major_id
  ├─ FK: subject_id → subjects
  └─ 1:N users（このプロジェクトでは逆参照）

subjects (学科)
  ├─ PK: subject_id
  ├─ FK: faculty_id → faculties
  └─ 1:N majors（専攻）

faculties (学部)
  ├─ PK: faculty_id
  └─ 1:N subjects（学科）
```

---

## 🔐 第4章：制約（Constraints）とは？

**定義:**
```
データベースに対する「ルール」
ルールを守らないデータは入れさせない
```

### **ON DELETE CASCADE**

**例え:**
```
友達グループを削除したら、グループ内のメッセージも自動削除される

DELETE FROM groups WHERE group_id = 1
  ↓
  自動的に
  ↓
DELETE FROM dms WHERE group_id = 1
```

**コード:**
```php
$table->foreignId('group_id')
    ->constrained('groups', 'group_id')
    ->cascadeOnDelete();  // ← ここが ON DELETE CASCADE
```

### **ON DELETE SET NULL**

**例え:**
```
返信元メッセージを削除しても、返信メッセージは残す
（ただし、返信元IDは NULL になる）

DELETE FROM dms WHERE dm_id = 100
  ↓
  dms テーブル内で parent_id = 100 となっているレコードの
  parent_id を NULL に変更する
```

**コード:**
```php
$table->foreignId('parent_id')
    ->nullable()
    ->constrained('dms', 'dm_id')
    ->nullOnDelete();  // ← ここが ON DELETE SET NULL
```

### **UQ（ユニーク制約）**

**例え:**
```
同じユーザーが同じ投稿に複数回いいねできない

nices テーブル
┌────┬────────┬────────┐
│ id │ prc_id │user_id │
├────┼────────┼────────┤
│ 1  │ 5      │ 1      │ ← ユーザー1が投稿5にいいね
│ 2  │ 5      │ 2      │
│ 3  │ 5      │ 1      │ ← ❌ ユーザー1が投稿5に再度いいね？拒否される
└────┴────────┴────────┘

UNIQUE KEY (prc_id, user_id)
= (投稿ID, ユーザーID) のペアは重複してはいけない
```

---

## 💾 第5章：SQL って何？

**定義:**
```
SQL = データベースに指令を出すための言語
```

### **基本的な 4つの操作（CRUD）**

#### **C: CREATE（データ作成）**
```sql
INSERT INTO users (user_id, name, age) 
VALUES (1, '太郎', 20);

実行後：
┌────────┬──────┬─────┐
│user_id │name  │ age │
├────────┼──────┼─────┤
│ 1      │ 太郎 │ 20  │ ← 追加された
└────────┴──────┴─────┘
```

#### **R: READ（データ読み取り）**
```sql
SELECT * FROM users WHERE user_id = 1;

実行後：
┌────────┬──────┬─────┐
│user_id │name  │ age │
├────────┼──────┼─────┤
│ 1      │ 太郎 │ 20  │ ← 検索結果
└────────┴──────┴─────┘
```

#### **U: UPDATE（データ更新）**
```sql
UPDATE users SET age = 21 WHERE user_id = 1;

実行後：
┌────────┬──────┬─────┐
│user_id │name  │ age │
├────────┼──────┼─────┤
│ 1      │ 太郎 │ 21  │ ← age が更新された
└────────┴──────┴─────┘
```

#### **D: DELETE（データ削除）**
```sql
DELETE FROM users WHERE user_id = 1;

実行後：
┌────────┬──────┬─────┐
│user_id │name  │ age │
├────────┼──────┼─────┤
│ (空)   │      │     │ ← ユーザー1が削除された
└────────┴──────┴─────┘
```

---

## 🔄 第6章：Laravel Eloquent（ORM）とは？

**定義:**
```
Eloquent = PHP でデータベースを扱うためのツール
直接 SQL を書かなくても、PHP で操作できる
```

### **SQL vs Eloquent**

**SQL を直接書く場合：**
```php
$user = DB::select('SELECT * FROM users WHERE user_id = 1');
```

**Eloquent を使う場合（読みやすい）：**
```php
$user = User::find(1);
```

### **Eloquent でのリレーション操作**

```php
// ユーザーの全投稿を取得
$posts = $user->prcs()->get();

// サークルの全メンバーを取得
$members = $circle->users()->get();

// 投稿へのいいね数を取得
$likeCount = $post->nices()->count();
```

---

## 🎓 まとめ：全体の流れ

```
┌─────────────────────────────────────────────────────┐
│ 1. ユーザーが「投稿作成」をクリック                   │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 2. Controller が リクエストを受け取る                 │
│    (PrcController::store)                          │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 3. FormRequest がバリデーション                      │
│    (StorePrcRequest::rules)                        │
│    「投稿内容は必須か」「1000字以内か」をチェック     │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 4. Service がビジネスロジックを実行                  │
│    (PostService::createPost)                       │
│    - Prc モデルを作成                               │
│    - 画像をアップロード                              │
│    - Eager Loading で関連データを取得               │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 5. Model が データベースに保存                       │
│    (Prc::create)                                   │
│    ↓                                                │
│    INSERT INTO prcs (user_id, sentence, ...)       │
│    VALUES (1, '...',  ...)                         │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 6. Resource がレスポンスに変換                       │
│    (PostResource::toArray)                         │
│    データベースのレコード → JSON                      │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 7. ユーザーに JSON レスポンスが返される              │
│    { "id": 1, "sentence": "...", "user": {...} }  │
└─────────────────────────────────────────────────────┘
```

---

## 📚 学習リソース推奨順

### **初心者向けステップ**

**1. テーブル設計を理解する（最初）**
```
← 今ここ
学部 → 学科 → 専攻 → ユーザー
      1:N   1:N   N:1
```

**2. SQL の基本を学ぶ（次）**
```
SELECT, INSERT, UPDATE, DELETE
WHERE, JOIN, GROUP BY
```

**3. Eloquent の使い方を学ぶ（その次）**
```
Model::find()
Model::where()
Model::with() (Eager Loading)
```

**4. Laravel のアーキテクチャを理解する**
```
Controller → Service → Model → Database
          Request    Eloquent
```

---

## ❓ よくある質問

### **Q1: なぜテーブルを分ける必要があるの？**

```
A: データの重複を減らして、修正を簡単にするため
   + 統計が正確になる
   + 整合性が保たれる
```

### **Q2: インデックスを追加するとどのくらい速くなるの？**

```
A: 数百倍（100-1000倍）速くなることも
   
   Before: 1000万件をシーケンシャル検索 → 1秒
   After: インデックスで即座にアクセス → 1ms（1000倍！）
```

### **Q3: 正規化するとクエリが複雑になるけど大丈夫？**

```
A: その通り。ただし：
   
   ✅ Eloquent が自動でジョインしてくれる
   ✅ with('relation') で簡単に書ける
   ✅ 複雑さより正確性を優先すべき
```

### **Q4: このプロジェクトでなぜ学科と専攻を分けるの？**

```
A: 将来的な拡張を考えて：
   
   学科に：「開講時間」「担当教授」など情報を追加したい
   → 分けておくと容易
   
   統計を取りたい：
   → 「学科ごとのユーザー数」などが自動的に集計できる
```

---

**これで、ここまでのリファクタリングとDB設計の意味が理解できましたね！**

**次のステップ:**
1. このドキュメントを何度も読む（1週間に1回）
2. 実際にマイグレーションとSeederを書いてみる
3. SQL を直接実行してみる
4. Eloquent でクエリを書いてみる

頑張ってください！🚀
