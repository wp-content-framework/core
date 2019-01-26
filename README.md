# WP Content Framework

[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: >=5.6](https://img.shields.io/badge/PHP-%3E%3D5.6-orange.svg)](http://php.net/)
[![WordPress: >=3.9.3](https://img.shields.io/badge/WordPress-%3E%3D3.9.3-brightgreen.svg)](https://wordpress.org/)

WordPressのプラグインやテーマ開発用のフレームワークです。

# 要件
- PHP 5.6 以上
- WordPress 3.9.3 以上

# 手順

## プラグインからの利用

1. プラグインフォルダの作成  
wp-content/plugins フォルダに プラグイン用のフォルダを作成  
例：wp-content/plugins/example

2. プラグインファイルの作成  
作成したプラグインフォルダに適当なPHPファイル　(例：autoload.php) を作成  
[標準プラグイン情報](https://wpdocs.osdn.jp/%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%E3%81%AE%E4%BD%9C%E6%88%90#.E6.A8.99.E6.BA.96.E3.83.97.E3.83.A9.E3.82.B0.E3.82.A4.E3.83.B3.E6.83.85.E5.A0.B1)  
を参考にプラグインの情報を入力

3. このライブラリのインストール  
composer を使用してインストールします。  
作成したプラグインフォルダで以下のコマンドを実行します。  
``` composer require wp-content-framework/core ```  

4. ライブラリの使用  
作成したプラグインファイルにライブラリを使用する記述を追記します。  
プラグインファイルはおおよそ以下のようなものになります。

```
<?php
/*
Plugin Name: example
Plugin URI:
Description: Plugin Description
Author: example
Version: 0.0.0
Author URI: http://example.com/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

@require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

define( 'EXAMPLE_PLUGIN', 'Example_Plugin' );

WP_Framework::get_instance( EXAMPLE_PLUGIN, __FILE__ );
```

最終的なプラグインの構成は以下のようなものになります。

```
example
    |
    - autoload.php
    |
    - functions.php
    |
    - assets
    |
    - configs
    |
    - languages
    |
    - src
       |
        - classes
       |     |
       |     - controllers
       |     |      |
       |     |      - admin
       |     |      |
       |     |      - api 
       |     |
       |     - models
       |     |
       |     - tests
       |
       - views 
           |
           - admin
               |
               - help
```

## テーマからの利用

1. テーマフォルダの作成  
wp-content/themes フォルダに テーマ用のフォルダを作成  
例：wp-content/themes/example

2. テーマ用CSSの作成  
作成したテーマフォルダに style.css を作成  
[テーマスタイルシート](https://wpdocs.osdn.jp/%E3%83%86%E3%83%BC%E3%83%9E%E3%81%AE%E4%BD%9C%E6%88%90#.E3.83.86.E3.83.BC.E3.83.9E.E3.82.B9.E3.82.BF.E3.82.A4.E3.83.AB.E3.82.B7.E3.83.BC.E3.83.88)
を参考にテーマの情報を入力

3. このライブラリのインストール  
composer を使用してインストールします。  
作成したプラグインフォルダで以下のコマンドを実行します。  
```composer require wp-content-framework/core```

4. ライブラリの使用  
テーマフォルダに functions.php を作成しライブラリを使用する記述を追記します。  
functions.php はおおよそ以下のようなものになります。

```
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

@require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

define( 'EXAMPLE_THEME', 'Example_Theme' );

WP_Framework::get_instance( EXAMPLE_THEME, __FILE__ );
```

最終的なテーマの構成は以下のようなものになります。

```
example
    |
    - style.css
    |
    - functions.php
    |
    - assets
    |
    - configs
    |
    - languages
    |
    - src
    |  |
    |   - classes
    |  |     |
    |  |     - controllers
    |  |     |      |
    |  |     |      - admin
    |  |     |      |
    |  |     |      - api 
    |  |     |
    |  |     - models
    |  |     |
    |  |     - tests
    |  |
    |  - views 
    |      |
    |      - admin
    |          |
    |          - help
    |
    - header.php
    - footer.php
    - index.php
    - searchform.php
    - sidebar.php
    ...
```

　  
複数のプラグイン及びテーマでこのライブラリを使用する場合、モジュールも含めて最新のものが自動的に使用されます。

## モジュール
必要に応じてモジュールを追加します。  
いくつかのモジュールは依存関係によって自動的にインストールされます。
* core  
最新のモジュールの読み込み機能などのコアの機能を提供します。  
  * 依存モジュール
    * common
* [common](https://github.com/wp-content-framework/common)  
共通で使用する機能を提供します。
  * 依存モジュール
    * core
* [db](https://github.com/wp-content-framework/db)  
データベースを扱う機能を提供します。
  * 依存モジュール
    * common
* [presenter](https://github.com/wp-content-framework/presenter)  
描画機能を提供します。
  * 依存モジュール
    * common  
* [cron](https://github.com/wp-content-framework/cron)   
cron機能を提供します。
  * 依存モジュール
    * common 
* [controller](https://github.com/wp-content-framework/controller)  
コントローラ機能を提供します。
  * 依存モジュール
    * presenter
* [admin](https://github.com/wp-content-framework/admin)  
管理画面に関する機能を提供します。
  * 依存モジュール
    * controller
* [api](https://github.com/wp-content-framework/api)  
APIに関する機能を提供します。
  * 依存モジュール
    * controller
* [upgrade](https://github.com/wp-content-framework/upgrade)  
更新に関する機能を提供します。
  * 依存モジュール
    * common
  * 関連モジュール
    * presenter  
    更新情報を表示する場合に必要です。
* [mail](https://github.com/wp-content-framework/mail)  
メール送信機能を提供します。
  * 依存モジュール
    * presenter
* [log](https://github.com/wp-content-framework/log)  
ログの機能を提供します。
  * 依存モジュール
    * db  
    * cron  
    * admin  
  * 関連モジュール
    * mail  
    メールを送信する場合に必要です。
* [post](https://github.com/wp-content-framework/post)  
投稿を扱う機能を提供します。
  * 依存モジュール
    * common
* [device](https://github.com/wp-content-framework/device)  
User Agent の判定などの機能を提供します。
  * 依存モジュール
    * common
* [social](https://github.com/wp-content-framework/social)  
ソーシャルログイン機能を提供します。
  * 依存モジュール
    * common
* [session](https://github.com/wp-content-framework/session)  
セッション機能を提供します。
  * 依存モジュール
    * common
* [custom_post](https://github.com/wp-content-framework/custom_post)  
カスタム投稿タイプに関する機能を提供します。
  * 依存モジュール
    * presenter
    * db
* [test](https://github.com/wp-content-framework/test)  
テスト機能を提供します。
  * 依存モジュール
    * admin

## 画面の追加
[admin](https://github.com/wp-content-framework/admin)  

## API の追加
[api](https://github.com/wp-content-framework/api)  

## filter の追加
今後ドキュメント追加予定

## cron の追加
[cron](https://github.com/wp-content-framework/cron)  

## カスタム投稿タイプの追加
[custom_post](https://github.com/wp-content-framework/custom_post)  

## コンフィグ
### 設定
- configs/setting.php

設定例：
```
// priority => 詳細
'10' => array(

    // 設定グループ => 詳細
    'Performance' => array(
    
        // priority => 詳細
        '10' => array(
        
            // 設定名 => 詳細
            'minify_js'  => array(
                // 説明
                'label'   => 'Whether to minify js which generated by this plugin',
                // タイプ (bool or int or float or string)
                'type'    => 'bool', // [default = string]
                // デフォルト値
                'default' => true,
            ),
            'minify_css' => array(
                'label'   => 'Whether to minify css which generated by this plugin',
                'type'    => 'bool',
                'default' => true,
            ),
        ),
    ),
),
```

設定ページで設定可能になります。  
プログラムで使用するには以下のようにします。
```
$this->apply_filters( 'minify_js' ) // true or false

if ( $this->apply_filters( 'minify_js' ) ) {
    // ...
}
```

### フィルタ
- configs/filter.php  
今後追加予定

### DB
- configs/db.php  
[db](https://github.com/wp-content-framework/db)  

### 権限
- configs/capability.php  
今後追加予定

## 基本設定
- configs/settings.php

|設定値|説明|
|---|---|
|admin_menu_position|管理画面のメニューの表示位置|

## サンプルプラグイン
[CSRF検知プラグイン](https://github.com/technote-space/csrf-detector)  

# Author

[technote-space](https://github.com/technote-space)
