# ER図
erDiagram
    users ||--o{ circles : "created"
    users ||--o{ dms : "sends"
    users ||--o{ dms : "receives"
    users ||--o{ prcs : "posts"
    users ||--o{ nices : "likes"
    users ||--|| profiles : "has"
    users ||--o{ groupmembers : "joins"
    users ||--o{ circle_users : "joins"

    circles ||--o{ groups : "has"
    circles ||--o{ circle_users : "has members"
    circles ||--o{ dms : "context for"
    circles ||--o{ prcs : "context for"

    groups ||--o{ groupmembers : "has members"
    groups ||--o{ dms : "context for"

    dms ||--o{ images_and_videos : "contains"
    dms ||--o{ dms : "replies to"

    prcs ||--o{ images_and_videos : "contains"
    prcs ||--o{ nices : "receives"
    prcs ||--o{ prcs : "replies to"

    profiles ||--o{ prcs : "has posts"
    
    users {
        bigint user_id PK
        string mail
        text name
        int age
        int grade
    }

    circles {
        bigint circle_id PK
        string circle_name
        int owner_id
        string category
    }

    groups {
        bigint group_id PK
        bigint circle_id FK
        string group_name
    }

    dms {
        bigint dm_id PK
        bigint circle_id FK
        bigint group_id FK
        bigint sender_id FK
        bigint receiver_id FK
        text message_text
    }

    prcs {
        bigint prc_id PK
        bigint user_id FK
        bigint circle_id FK
        bigint parent_id
        text sentence
    }

    images_and_videos {
        bigint image_and_video_id PK
        bigint prc_id FK
        bigint dm_id FK
        text video
        text image
    }

    nices {
        bigint nice_id PK
        int prc_id
        int user_id
    }

    groupmembers {
        bigint groupmember_id PK
        bigint user_id FK
        bigint group_id FK
    }

    circle_users {
        bigint circle_user_id PK
        bigint circle_id FK
        bigint user_id FK
    }

    profiles {
        bigint profile_id PK
        bigint user_id FK
    }
# フロー図
    flowchart TD
    %% ノードの定義
    Start((開始))
    Login[ログイン画面 /]
    Register[新規登録 /newlogin]
    EmailVerify{メール認証}
    
    Home[ホーム /home]
    Profile[プロフィール /profile]
    ProfileEdit[プロフィール編集 /profile/edit]
    
    PostCreate[投稿作成 /post]
    
    CircleList[サークル一覧 /circle]
    CircleCreate[サークル作成 /circle/create]
    CircleDetail[サークル詳細 /circle/:id]
    CirclePost[サークル内投稿]
    CircleDM[サークル内DM]
    CircleJoin{参加リクエスト}
    
    DMList[DM一覧 /dmlist]
    DMDetail[DMチャット /dm]

    %% フローの定義
    Start --> Login
    Start --> Register
    Register --> EmailVerify
    EmailVerify -- 認証OK --> Login
    
    Login -- 認証成功 --> Home
    
    Home --> Profile
    Profile --> ProfileEdit
    ProfileEdit --> Profile
    
    Home --> PostCreate
    PostCreate -- 投稿完了 --> Home
    
    Home --> CircleList
    CircleList --> CircleCreate
    CircleList --> CircleDetail
    
    CircleDetail --> CircleJoin
    CircleJoin -- 承認/参加 --> CirclePost
    CircleJoin -- 承認/参加 --> CircleDM
    
    Home --> DMList
    DMList --> DMDetail
    
    %% スタイリング (オプション)
    classDef main fill:#f9f,stroke:#333,stroke-width:2px;
    classDef sub fill:#bbf,stroke:#333,stroke-width:1px;
    class Home,Login,Register main;
    class Profile,CircleList,DMList sub;