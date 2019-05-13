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

if ( defined( 'EXAMPLE_PLUGIN' ) ) {
	return;
}

define( 'EXAMPLE_PLUGIN', 'Example_Plugin' );

@require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

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

if ( defined( 'EXAMPLE_THEME' ) ) {
	return;
}

define( 'EXAMPLE_THEME', 'Example_Theme' );

@require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

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
* [core](https://github.com/wp-content-framework/core)    
最新のモジュールの読み込み機能などのコアの機能を提供します。  
  * 依存モジュール
    * common
    * cache
* [common](https://github.com/wp-content-framework/common)  
共通で使用する機能を提供します。
* [cache](https://github.com/wp-content-framework/cache)  
キャッシュ機能を提供します。
  * 関連モジュール
    * cron  
    期限切れのキャッシュを定期的に削除する場合に必要です。
* [db](https://github.com/wp-content-framework/db)  
データベースを扱う機能を提供します。
* [presenter](https://github.com/wp-content-framework/presenter)  
描画機能を提供します。
* [view](https://github.com/wp-content-framework/view)  
共通の描画テンプレートを提供します。
  * 依存モジュール
    * presenter  
* [cron](https://github.com/wp-content-framework/cron)   
cron機能を提供します。
* [controller](https://github.com/wp-content-framework/controller)  
コントローラ機能を提供します。
  * 依存モジュール
    * presenter
* [admin](https://github.com/wp-content-framework/admin)  
管理画面に関する機能を提供します。
  * 依存モジュール
    * controller
    * view
* [api](https://github.com/wp-content-framework/api)  
APIに関する機能を提供します。
  * 依存モジュール
    * controller
* [update](https://github.com/wp-content-framework/update)  
更新情報を表示する機能を提供します。
  * 依存モジュール
    * presenter  
* [update_check](https://github.com/wp-content-framework/update_check)  
公式ディレクトリ以外で更新を行う機能を提供します。
* [upgrade](https://github.com/wp-content-framework/upgrade)  
アップグレードに関する機能を提供します。
  * 関連モジュール
    * log  
    アップグレード履歴を保存する場合に必要です。
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
* [device](https://github.com/wp-content-framework/device)  
User Agent の判定などの機能を提供します。
* [social](https://github.com/wp-content-framework/social)  
ソーシャルログイン機能を提供します。
  * 依存モジュール
    * session
* [session](https://github.com/wp-content-framework/session)  
セッション機能を提供します。
* [custom_post](https://github.com/wp-content-framework/custom_post)  
カスタム投稿タイプに関する機能を提供します。
  * 依存モジュール
    * db
    * session
    * admin
    * api
* [test](https://github.com/wp-content-framework/test)  
テスト機能を提供します。
  * 依存モジュール
    * admin

## 画面の追加
[admin](https://github.com/wp-content-framework/admin#%E7%94%BB%E9%9D%A2%E3%81%AE%E8%BF%BD%E5%8A%A0)  

## API の追加
[api](https://github.com/wp-content-framework/api#api-%E3%81%AE%E8%BF%BD%E5%8A%A0)  

## filter の追加
今後ドキュメント追加予定

## cron の追加
[cron](https://github.com/wp-content-framework/cron#cron-%E3%81%AE%E8%BF%BD%E5%8A%A0)  

## カスタム投稿タイプの追加
[custom_post](https://github.com/wp-content-framework/custom_post#%E3%82%AB%E3%82%B9%E3%82%BF%E3%83%A0%E6%8A%95%E7%A8%BF%E3%82%BF%E3%82%A4%E3%83%97%E3%81%AE%E8%BF%BD%E5%8A%A0)  

## テストの追加
[test](https://github.com/wp-content-framework/test#%E3%83%86%E3%82%B9%E3%83%88%E3%81%AE%E8%BF%BD%E5%8A%A0)  

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

## デフォルトの動作の上書き
今後追加予定

## 基本設定
- configs/config.php

|設定値|説明|
|---|---|
|required_php_version|動作に必要なPHPの要求バージョン \[default = 5.6]|
|required_wordpress_version|動作に必要なWordPressの要求バージョン \[default = 3.9.3]|
|filter_separator|filter prefix の separator \[default = '/']|

- configs/settings.php

|設定値|説明|
|---|---|
|admin_menu_position|管理画面のメニューの表示位置|

## サンプルプラグイン
[関連記事提供用プラグイン](https://github.com/technote-space/wp-related-post-jp)  
[Gutenberg用文字修飾 プラグイン](https://github.com/technote-space/add-richtext-toolbar-button)  
[Marker Animation プラグイン](https://github.com/technote-space/marker-animation)  
[Yahoo! API を使用した校正支援プラグイン](https://github.com/technote-space/y-proofreading)  
[Gutenbergのブロックを一時的に非表示にするプラグイン](https://github.com/technote-space/hide-blocks-temporarily)  
[CSRF検知プラグイン](https://github.com/technote-space/csrf-detector)  
[Contact Form 7 拡張用プラグイン](https://github.com/technote-space/contact-form-7-huge-file-upload)  
[Gutenberg サンプル用プラグイン](https://github.com/technote-space/gutenberg-samples)  

# Author

[GitHub (Technote)](https://github.com/technote-space)  
[Blog](https://technote.space)
