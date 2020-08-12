<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!defined('FTP_ERR_SERVER_DISABLED')) {
	define('FTP_ERR_SERVER_DISABLED', -100);
	define('FTP_ERR_CONFIG_OFF', -101);
	define('FTP_ERR_CONNECT_TO_SERVER', -102);
	define('FTP_ERR_USER_NO_LOGGIN', -103);
	define('FTP_ERR_CHDIR', -104);
	define('FTP_ERR_MKDIR', -105);
	define('FTP_ERR_SOURCE_READ', -106);
	define('FTP_ERR_TARGET_WRITE', -107);
}

if(!defined('FTP_ERR_COS_CONFIG')) {
    define('FTP_ERR_COS_CONFIG', -108);
}


class discuz_ftp_ext extends discuz_ftp
{

	var $enabled = false;
	var $config = array();
	var $config_ext = array();

	var $func;
	var $connectid;
	var $_error;

	var $curstorage = '';
	var $curobj;

	const FTP_CURSTORAGE = 'qcloud';


	function __construct($config = array())
    {
        global $_G;
        $this->set_error(0);
        $this->config = !$config ? getglobal('setting/ftp') : $config;
        $this->enabled = false;
        if (empty($this->config['on'])) {
            $this->set_error(FTP_ERR_CONFIG_OFF);
        } else {
            // 开启腾讯与远程附件功能
            if (isset($this->config['tencentcos']) && $this->config['tencentcos'] === '1') {
                $this->curstorage = self::FTP_CURSTORAGE;
                C::import('storage/'.$this->curstorage, 'vendor', true, true);
                $this->curobj = new QcloudBass($this->config['secretid'], $this->config['secretkey'], $this->config['region'], $this->config['bucket']);
                $this->curobj->set_debug_mode(FALSE);
                !empty($this->curobj) && $this->enabled = true;
                return true;
            }

            $this->func = $this->config['ssl'] && function_exists('ftp_ssl_connect') ? 'ftp_ssl_connect' : 'ftp_connect';
            if($this->func == 'ftp_connect' && !function_exists('ftp_connect')) {
                $this->set_error(FTP_ERR_SERVER_DISABLED);
            } else {
                $this->config['host'] = discuz_ftp::clear($this->config['host']);
                $this->config['port'] = intval($this->config['port']);
                $this->config['ssl'] = intval($this->config['ssl']);
                $this->config['username'] = discuz_ftp::clear($this->config['username']);
                $this->config['password'] = authcode($this->config['password'], 'DECODE', md5(getglobal('config/security/authkey')));
                $this->config['timeout'] = intval($this->config['timeout']);
                $this->enabled = true;
            }
        }
    }

	function clear($str) {
		$rt = parent::clear($str);
		return str_replace(array("//"), array("/"), $rt);
	}

	function upload($source, $target) {
		if($this->error()) {
			return 0;
		}
		switch($this->curstorage) {
            case 'qcloud':
                $target = substr($target, 0, 1) == "/" ? $target : '/'.$target;
                $succeed = $this->curobj->uploadFileToCos($source, $target);
                return $succeed ? 1 : 0;;
            case 'grand':
				return 0;
		}

		$old_dir = $this->ftp_pwd();
		$dirname = dirname($target);
		$filename = basename($target);
		if(!$this->ftp_chdir($dirname)) {
			if($this->ftp_mkdir($dirname)) {
				$this->ftp_chmod($dirname);
				if(!$this->ftp_chdir($dirname)) {
					$this->set_error(FTP_ERR_CHDIR);
				}
				$this->ftp_put('index.htm', getglobal('setting/attachdir').'/index.htm', FTP_BINARY);
			} else {
				$this->set_error(FTP_ERR_MKDIR);
			}
		}

		$res = 0;
		if(!$this->error()) {
			if($fp = @fopen($source, 'rb')) {
				$res = $this->ftp_fput($filename, $fp, FTP_BINARY);
				@fclose($fp);
				!$res && $this->set_error(FTP_ERR_TARGET_WRITE);
			} else {
				$this->set_error(FTP_ERR_SOURCE_READ);
			}
		}

		$this->ftp_chdir($old_dir);

		return $res ? 1 : 0;
	}

    function check_connect($setting) {
        if (isset($setting['ftp']['tencentcos']) && $setting['ftp']['tencentcos'] === '1') {
            if (!$this->curobj->checkCosBucket($setting)) {
                $this->set_error(FTP_ERR_COS_CONFIG);
            }
            return true;
        } else {
            $this->set_error(FTP_ERR_COS_CONFIG);
        }

    }

	function connect() {
		if(!$this->enabled || empty($this->config)) {
			return 0;
		} else {

            if($this->curstorage) return true;
			return $this->ftp_connect(
				$this->config['host'],
				$this->config['username'],
				$this->config['password'],
				$this->config['attachdir'],
				$this->config['port'],
				$this->config['timeout'],
				$this->config['ssl'],
				$this->config['pasv']
				);
		}

	}

	function ftp_close() {
		if($this->curstorage) return true;
		return @ftp_close($this->connectid);
	}

	function ftp_delete($path) {
		$path = discuz_ftp::clear($path);
		switch($this->curstorage) {
            case 'qcloud':
                $path = substr($path, 0, 1) == "/" ? substr($path, 1) : $path;
                return $this->curobj->deleteRemoteAttachment($path);
			case 'grand':
				return 0;
		}
		return @ftp_delete($this->connectid, $path);
	}

}
