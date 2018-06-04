<?php

class Session
{
    public function __construct()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

    }

    public function has($name)
    {
        if (is_array($name)) {
            foreach ($name as $v) {
                if (empty($_SESSION[$v])) {
                    return false;
                }
            }
            return true;
        } else {
            return isset($_SESSION[$name]);
        }
    }

    public function set($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $_SESSION[$k] = $v;
            }

        } else {
            $_SESSION[$name] = $value;
        }
        return true;
    }

    public function delete($name)
    {
        if (is_array($name)) {
            unset($_SESSION[$v]);

        } elseif (isset($name)) {
            unset($_SESSION[$name]);
        } else {
            $_SESSION = [];
        }
        return true;
    }

    public static function destroy()
    {
        if (!empty($_SESSION)) {
            $_SESSION = [];
        }
        session_unset();
        // session_destroy();
    }
}
