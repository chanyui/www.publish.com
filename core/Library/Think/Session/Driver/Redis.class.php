<?php
/**
 * ClassName: Redis.class.php
 * User: wangyu
 * Date: 2017/1/19
 * Time: 17:42
 */

namespace Think\Session\Driver;


class Redis
{
    /**
     * Redis���
     */
    private $handler;
    private $get_result;

    public function __construct()
    {
        if (!extension_loaded('redis')) {
            E(L('_NOT_SUPPERT_') . ':redis');
        }
        if (empty($options)) {
            $options = array(
                'host' => C('SESSION_REDIS_HOST') ? C('SESSION_REDIS_HOST') : '127.0.0.1',
                'port' => C('SESSION_REDIS_PORT') ? C('SESSION_REDIS_PORT') : 6379,
                'timeout' => C('SESSION_CACHE_TIME') ? C('SESSION_CACHE_TIME') : false,
                'persistent' => C('SESSION_PERSISTENT') ? C('SESSION_PERSISTENT') : false,
                'auth' => C('SESSION_REDIS_AUTH') ? C('SESSION_REDIS_AUTH') : false,
            );
        }
        $options['host'] = explode(',', $options['host']);
        $options['port'] = explode(',', $options['port']);
        $options['auth'] = explode(',', $options['auth']);
        foreach ($options['host'] as $key => $value) {
            if (!isset($options['port'][$key])) {
                $options['port'][$key] = $options['port'][0];
            }
            if (!isset($options['auth'][$key])) {
                $options['auth'][$key] = $options['auth'][0];
            }
        }
        $this->options = $options;
        $expire = C('SESSION_EXPIRE');
        $this->options['expire'] = isset($expire) ? (int)$expire : (int)ini_get('session.gc_maxlifetime');;
        $this->options['prefix'] = isset($options['prefix']) ? $options['prefix'] : C('SESSION_PREFIX');
        $this->handler = new \Redis;
    }

    /**
     * ����Redis�����
     * @access public
     * @param bool $is_master : �Ƿ�������������
     */
    public function connect($is_master = true)
    {
        if ($is_master) {
            $i = 0;
        } else {
            $count = count($this->options['host']);
            if ($count == 1) {
                $i = 0;
            } else {
                $i = rand(1, $count - 1);   //����ӷ��������ѡ��
            }
        }
        $func = $this->options['persistent'] ? 'pconnect' : 'connect';
        try {
            if ($this->options['timeout'] === false) {
                $result = $this->handler->$func($this->options['host'][$i], $this->options['port'][$i]);
                if (!$result)
                    throw new \Think\Exception('Redis Error', 100);
            } else {
                $result = $this->handler->$func($this->options['host'][$i], $this->options['port'][$i], $this->options['timeout']);
                if (!$result)
                    throw new \Think\Exception('Redis Error', 101);
            }
            if ($this->options['auth'][$i]) {
                $result = $this->handler->auth($this->options['auth'][$i]);
                if (!$result) {
                    throw new \Think\Exception('Redis Error', 102);
                }
            }
        } catch (\Exception $e) {
            exit('Error Message:' . $e->getMessage() . '<br>Error Code:' . $e->getCode() . '');
        }
    }

    /**
     * +----------------------------------------------------------
     * ��Session
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $savePath
     * @param mixed $sessName
     * +----------------------------------------------------------
     */
    public function open($savePath, $sessName)
    {
        return true;
    }

    /**
     * +----------------------------------------------------------
     * �ر�Session
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     */
    public function close()
    {
        if ($this->options['persistent'] == 'pconnect') {
            $this->handler->close();
        }
        return true;
    }

    /**
     * +----------------------------------------------------------
     * ��ȡSession
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $sessID
     * +----------------------------------------------------------
     */
    public function read($sessID)
    {
        $this->connect(0);
        $this->get_result = $this->handler->get($this->options['prefix'] . $sessID);
        return $this->get_result;
    }

    /**
     * +----------------------------------------------------------
     * д��Session
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $sessID
     * @param String $sessData
     * +----------------------------------------------------------
     */
    public function write($sessID, $sessData)
    {
        if (!$sessData || $sessData == $this->get_result) {
            return true;
        }
        $this->connect(1);
        $expire = $this->options['expire'];
        $sessID = $this->options['prefix'] . $sessID;
        if (is_int($expire) && $expire > 0) {
            $result = $this->handler->setex($sessID, $expire, $sessData);
            $re = $result ? 'true' : 'false';
        } else {
            $result = $this->handler->set($sessID, $sessData);
            $re = $result ? 'true' : 'false';
        }
        return $result;
    }

    /**
     * +----------------------------------------------------------
     * ɾ��Session
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $sessID
     * +----------------------------------------------------------
     */
    public function destroy($sessID)
    {
        $this->connect(1);
        return $this->handler->delete($this->options['prefix'] . $sessID);
    }

    /**
     * +----------------------------------------------------------
     * Session ��������
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $sessMaxLifeTime
     * +----------------------------------------------------------
     */
    public function gc($sessMaxLifeTime)
    {
        return true;
    }

    /**
     * +----------------------------------------------------------
     * ��Session
     * +----------------------------------------------------------
     * @access public
     * +----------------------------------------------------------
     * @param string $savePath
     * @param mixed $sessName
     * +----------------------------------------------------------
     */
    public function execute()
    {
        session_set_save_handler(
            array(&$this, "open"),
            array(&$this, "close"),
            array(&$this, "read"),
            array(&$this, "write"),
            array(&$this, "destroy"),
            array(&$this, "gc")
        );
    }

    public function __destruct()
    {
        if ($this->options['persistent'] == 'pconnect') {
            $this->handler->close();
        }
        session_write_close();
    }
}