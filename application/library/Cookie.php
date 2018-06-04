<?php

class Cookie
{
    public function has($name)
    {
        return isset($_COOKIE[$name]);
    }

    public function get($name, $default = null)
    {
        if (is_array($name)) {
            foreach ($name as $k) {
                if (isset($_COOKIE[$k])) {
                    $data[$k] = $_COOKIE[$k];
                } else {
                    $data[$k] = $default;
                }
            }
            return $data;
        } else {
            if (isset($_COOKIE[$name])) {
                return $_COOKIE[$name];
            } else {
                return $default;
            }
        }
    }

    public function set($name, $value, $expire = 18100)
    {
        $expire = time() + $expire;
        if (is_array($name)) {
            foreach ($name as $k->$v) {
                setcookie($k, $v, $expire, '/');
                $_COOKIE[$k] = $v;
            }
        } else {
            // var_dump($name, $value, $expire, setcookie($name, $value, $expire));
            setcookie($name, $value, $expire, '/');
            $_COOKIE[$name] = $value;
        }
        return true;
    }

    public function del($name)
    {
        if (is_array($name)) {
            foreach ($name as $v) {
                setcookie($v, '', $_SERVER['REQUEST_TIME'] - 3600, '/');
                unset($_COOKIE[$v]);
            }
        } else {

            setcookie($name, '', $_SERVER['REQUEST_TIME'] - 3600, '/');
            unset($_COOKIE[$name]);
        }

        return true;
    }

}
