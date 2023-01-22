<?php
namespace tomk79\pickles2\px2ErrorReporter;
use Throwable;

/**
 * px2-clover
 */
class register{

	/** Picklesオブジェクト */
	private $px;

	/** プラグインオプション */
	private $options;

	/** px2-clover */
	private $clover;


	/**
	 * entry
	 *
	 * @param object $px Picklesオブジェクト
	 * @param object $options プラグイン設定
	 */
	static public function register( $px = null, $options = null ){
		if( count(func_get_args()) <= 1 ){

            $options = $px;

            // 例外ハンドラを設定する
            set_exception_handler(function(Throwable $exception) use ($options){
                echo "Uncaught exception: ", $exception->getMessage(), "\n";
                if( $options['realpath_log_dir'] ?? false && is_dir($options['realpath_log_dir']) ){
                    error_log(
                        "Uncaught exception: ".$exception->getMessage().' on '.$exception->getFile().' line:'.$exception->getLine()."\n",
                        3,
                        $options['realpath_log_dir'].'/error_report.log'
                    );
                }
            });

            // エラーハンドラを設定する
            set_error_handler(function($errno, $errstr, $errfile, $errline) use ($options){
                if( $options['realpath_log_dir'] ?? false && is_dir($options['realpath_log_dir']) ){
                    error_log(
                        'Error['.$errno.']: '.$errstr.' on '.$errfile.' line:'.$errline."\n",
                        3,
                        $options['realpath_log_dir'].'/error_report.log'
                    );
                }
 
                return false;
            });


			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		return;
	}

}
