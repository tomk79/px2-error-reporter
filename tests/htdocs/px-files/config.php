<?php
return call_user_func( function(){

	if( is_file(__DIR__.'/../../../.env') ){
		$dotenv = \Dotenv\Dotenv::createImmutable( __DIR__.'/../../../' );
		$dotenv->load();
	}

	// initialize
	$conf = new stdClass;

	// project
	$conf->name = 'Pickles 2 Error Reporter'; // サイト名
	$conf->domain = 'pickles2.com'; // ドメイン
	$conf->path_controot = '/'; // コンテンツルートディレクトリ

	// paths
	$conf->path_top = '/'; // トップページのパス(デフォルト "/")
	$conf->path_publish_dir = './px-files/dist/'; // パブリッシュ先ディレクトリパス
	$conf->public_cache_dir = '/caches/'; // 公開キャッシュディレクトリ
	$conf->path_files = '{$dirname}/{$filename}_files/'; // リソースディレクトリ(各コンテンツに対して1:1で関連付けられる)のパス
	$conf->contents_manifesto = '/common/contents_manifesto.ignore.php'; // Contents Manifesto のパス


	// directory index
	// `directory_index` は、省略できるファイル名のリストを設定します。
	$conf->directory_index = array(
		'index.html'
	);


	// system
	$conf->file_default_permission = '775'; // ファイルに適用されるデフォルトのパーミッション
	$conf->dir_default_permission = '775'; // ディレクトリに適用されるデフォルトのパーミッション
	$conf->filesystem_encoding = 'UTF-8'; // ファイルシステムの文字セット。ファイル名にマルチバイト文字を使う場合に参照されます。
	$conf->output_encoding = 'UTF-8'; // 出力文字エンコーディング名
	$conf->output_eol_coding = 'lf'; // 出力改行コード名 (cr|lf|crlf)
	$conf->session_name = 'PXSID'; // セッション名
	$conf->session_expire = 1800; // セッションの有効期間
	$conf->allow_pxcommands = 1; // PX Commands のウェブインターフェイスからの実行を許可
	$conf->default_timezone = 'Asia/Tokyo';



	// commands
	// Pickles2 が認識するコマンドのパスを設定します。
	// コマンドのパスが通っていない場合は、絶対パスで設定してください。
	$conf->commands = new stdClass;
	$conf->commands->php = 'php';
	$conf->path_phpini = null; // php.ini のパス。主にパブリッシュ時のサブクエリで使用する。



	/**
	 * paths_proc_type
	 *
	 * パスのパターン別に処理方法を設定します。
	 *
	 * - ignore = 対象外パス。Pickles 2 のアクセス可能範囲から除外します。このパスにへのアクセスは拒絶され、パブリッシュの対象からも外されます。
	 * - direct = 物理ファイルを、ファイルとして読み込んでから加工処理を通します。 (direct以外の通常の処理は、PHPファイルとして `include()` されます)
	 * - pass = 物理ファイルを、そのまま無加工で出力します。 (デフォルト)
	 * - その他 = extension名
	 *
	 * パターンは先頭から検索され、はじめにマッチした設定を採用します。
	 * ワイルドカードとして "*"(アスタリスク) が使用可能です。
	 * 部分一致ではなく、完全一致で評価されます。従って、ディレクトリ以下すべてを表現する場合は、 `/*` で終わるようにしてください。
	 *
	 * extensionは、 `$conf->funcs->processor` に設定し、設定した順に実行されます。
	 * 例えば、 '*.html' => 'html' にマッチしたリクエストは、
	 * $conf->funcs->processor->html に設定したプロセッサのリストに沿って、上から順に処理されます。
	 */
	$conf->paths_proc_type = array(
		'/.htaccess' => 'ignore' ,
		'/.px_execute.php' => 'ignore' ,
		'/px-files/*' => 'ignore' ,
		'/composer.json' => 'ignore' ,
		'/composer.lock' => 'ignore' ,
		'/README.md' => 'ignore' ,
		'/vendor/*' => 'ignore' ,
		'*/.DS_Store' => 'ignore' ,
		'*/Thumbs.db' => 'ignore' ,
		'*/.svn/*' => 'ignore' ,
		'*/.git/*' => 'ignore' ,
		'*/.gitignore' => 'ignore' ,

		'*.ignore/*' => 'ignore' ,
		'*.ignore.*' => 'ignore' ,
		'*.pass/*' => 'pass' ,
		'*.pass.*' => 'pass' ,
		'*.direct/*' => 'direct' ,
		'*.direct.*' => 'direct' ,

		'*.html' => 'html' ,
		'*.htm' => 'html' ,
		'*.css' => 'css' ,
		'*.js' => 'js' ,
		'*.png' => 'direct' ,
		'*.jpg' => 'direct' ,
		'*.gif' => 'direct' ,
		'*.svg' => 'direct' ,
	);


	// -------- functions --------

	$conf->funcs = new stdClass;

	// funcs: Before sitemap
	// サイトマップ読み込みの前に実行するプラグインを設定します。
	$conf->funcs->before_sitemap = [
		// px2-error-reporter
		\tomk79\pickles2\px2ErrorReporter\register::register(array(
			"realpath_log_dir" => __DIR__.'/_sys/ram/logs/error_reporter/',
		)),

		// px2-clover
		\tomk79\pickles2\px2clover\register::clover(array(
			"protect_preview" => false, // プレビューに認証を要求するか？
		)),

		// PX=clearcache
		'picklesFramework2\commands\clearcache::register' ,

		 // PX=config
		'picklesFramework2\commands\config::register' ,

		 // PX=phpinfo
		'picklesFramework2\commands\phpinfo::register' ,

		// sitemapExcel
		'tomk79\pickles2\sitemap_excel\pickles_sitemap_excel::exec' ,

		// px2-serve
		\tomk79\pickles2\px2serve\serve::register(),
	];

	// funcs: Before content
	// サイトマップ読み込みの後、コンテンツ実行の前に実行するプラグインを設定します。
	$conf->funcs->before_content = [
		// PX=api
		'picklesFramework2\commands\api::register' ,

		// PX=publish
		'picklesFramework2\commands\publish::register' ,

		// PX=px2dthelper
		'tomk79\pickles2\px2dthelper\main::register',

	];


	// processor
	// コンテンツの種類に応じた処理の設定を行います。
	// `$conf->funcs->processor->{$paths_proc_typeに設定した処理名}` のように設定します。
	// それぞれの処理は配列で、複数登録することができます。処理は上から順に実行されます。
	// Tips: テーマは、html に対するプロセッサの1つとして実装されています。
	$conf->funcs->processor = new stdClass;

	$conf->funcs->processor->html = [
		// ページ内目次を自動生成する
		'picklesFramework2\processors\autoindex\autoindex::exec' ,

		// px2-path-resolver - 共通コンテンツのリンクやリソースのパスを解決する
		//   このAPIは、サイトマップCSV上で path と content が異なるパスを参照している場合に、
		//   相対的に記述されたリンクやリソースのパスがあわなくなる問題を解決します。
		'tomk79\pickles2\pathResolver\main::resolve_common_contents()' ,

		// テーマ
		'theme'=>'tomk79\pickles2\multitheme\theme::exec('.json_encode([
			'param_theme_switch'=>'THEME',
			'cookie_theme_switch'=>'THEME',
			'path_theme_collection'=>'./px-files/themes/',
			'attr_bowl_name_by'=>'data-contents-area',
			'default_theme_id'=>'pickles2'
		]).')' ,

		// Apache互換のSSIの記述を解決する
		'picklesFramework2\processors\ssi\ssi::exec' ,

		// DEC変換処理の実行
		//   Pickles2の状態を参照し、自動的に処理を振り分けます。
		//   パブリッシュする場合、DECコメントを削除します。
		//   プレビューの場合、DECライブラリを埋め込み、
		//   URIパラメータからDECの表示・非表示を切り替えられるようにします。
		'tomk79\pickles2\dec\main::exec()' ,

		// 属性 data-contents-area を削除する
		'tomk79\pickles2\remove_attr\main::exec('.json_encode(array(
			"attrs"=>array(
				'data-contents-area',
			) ,
		)).')' ,

		// broccoli-receive-message スクリプトを挿入
		'tomk79\pickles2\px2dthelper\broccoli_receive_message::apply('.json_encode( array(
			// 許可する接続元を指定
			'enabled_origin'=>array(
			)
		) ).')' ,

		// output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		'picklesFramework2\processors\encodingconverter\encodingconverter::exec' ,
	];

	$conf->funcs->processor->css = [
		// output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		'picklesFramework2\processors\encodingconverter\encodingconverter::exec' ,
	];

	$conf->funcs->processor->js = [
		// output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		'picklesFramework2\processors\encodingconverter\encodingconverter::exec' ,
	];

	$conf->funcs->processor->md = [
		// Markdown文法を処理する
		'picklesFramework2\processors\md\ext::exec' ,

		// html の処理を追加
		$conf->funcs->processor->html ,
	];

	$conf->funcs->processor->scss = [
		// SCSS文法を処理する
		'picklesFramework2\processors\scss\ext::exec' ,

		// css の処理を追加
		$conf->funcs->processor->css ,
	];


	// funcs: Before output
	// 最終出力の直前で実行される処理を設定します。
	// この処理は、拡張子によらずすべてのリクエストが対象です。
	// (HTMLの場合は、テーマの処理の後のコードが対象になります)
	$conf->funcs->before_output = [
		// px2-path-resolver - 相対パス・絶対パスを変換して出力する
		//   options
		//     string 'to':
		//       - relate: 相対パスへ変換
		//       - absolute: 絶対パスへ変換
		//       - pass: 変換を行わない(default)
		//     bool 'supply_index_filename':
		//       - true: 省略されたindexファイル名を補う
		//       - false: 省略できるindexファイル名を削除
		//       - null: そのまま (default)
		'tomk79\pickles2\pathResolver\main::exec('.json_encode(array(
			'to' => 'absolute' ,
			'supply_index_filename' => false
		)).')' ,

	];


	// -------- config for Plugins. --------
	// その他のプラグインに対する設定を行います。
	$conf->plugins = new stdClass;

	// config for Pickles2 Desktop Tool.
	$conf->plugins->px2dt = new stdClass;
	$conf->plugins->px2dt->paths_module_template = [
		"PlainHTMLElements" => "../../vendor/broccoli-html-editor/broccoli-module-plain-html-elements/modules/",
		"local" => "./px-files/modules/",
		"FESS" => "../../vendor/broccoli-html-editor/broccoli-module-fess/modules/"
	];
	$conf->plugins->px2dt->contents_area_selector = '[data-contents-area]'; // <- コンテンツエリアを識別するセレクタ(複数の要素がマッチしてもよい)
	$conf->plugins->px2dt->contents_bowl_name_by = 'data-contents-area'; // <- コンテンツエリアのbowl名を指定する属性名


	// -------- PHP Setting --------

	// [memory_limit]
	// PHPのメモリの使用量の上限を設定します。
	// 正の整数値で上限値(byte)を与えます。
	//     例: 1000000 (1,000,000 bytes)
	//     例: "128K" (128 kilo bytes)
	//     例: "128M" (128 mega bytes)
	// -1 を与えた場合、無限(システムリソースの上限まで)に設定されます。
	// サイトマップやコンテンツなどで、容量の大きなデータを扱う場合に調整してください。
	// @ini_set( 'memory_limit' , -1 );


	return $conf;
} );
