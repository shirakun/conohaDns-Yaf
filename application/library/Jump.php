<?php

trait Jump
{

    protected $_tpl = 'views/public/jump.tpl';

    protected $config;

    public function success($msg, $url = '', $wait = 3, $data = [])
    {
        if (empty($url)) {
            $url = $_SERVER['HTTP_REFERER'];
        }
        $this->redirect($msg, $url, $wait, 1, [], [], 302);
    }

    public function error($msg, $url = 'javascript:history.back(-1);', $wait = 3, $data = [])
    {
        if (is_null($url)) {
            $url = 'javascript:history.back(-1);';
        }
        $this->redirect($msg, $url, $wait, 0, [], [], 301);
    }

    /**
     * 重定向
     * @param mixed $msg 消息
     * @param string $url 跳转url
     * @param int $wait 跳转等待时间
     * @param int $status 状态
     * @param mixed $data 包含的数据
     * @param array $header 头部信息
     * @param int $http_code http状态码
     *
     */
    public function redirect($msg, $url = '', $wait = 3, $status = 1, $data = [], $header = [], $http_code = 301)
    {
        try {
            // if (!empty($url)) {
            //     header("Location: {$url}", true, $http_code);
            // } else {
            //     http_response_code($http_code);
            // }
            if (!empty($header)) {
                foreach ($header as $k => $v) {
                    header("{$k}: {$v}");
                }
            }
            $msg = [
                'status' => $status,
                'msg'    => $msg,
                'url'    => $url,
                'wait'   => $wait,
                'data'   => $data,
            ];
            $out_method = $this->getOutput() . 'Output';
            $msg        = $this->$out_method($msg, $http_code);
            exit($msg);

        } catch (\Exception $e) {
            http_response_code(500);
            exit($e->getMessage());
        }
    }

    /**
     * 获取输出方式
     */
    private function getOutput()
    {
        if ((new Yaf\Request\Http())->isXmlHttpRequest()) {
            return 'json';
        } else {
            if (empty($this->config)) {
                $this->config = Yaf\Application::app()->getConfig();
            }
            return $this->config->default_return_type ?? 'html';
        }

    }

    private function jsonOutPut($data, $http_code)
    {
        header("Content-type: application/json; charset=utf-8");
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function htmlOutput($data, $http_code)
    {
        http_response_code($http_code);
        header("Content-type: text/html; charset=utf-8");
        // if (!empty($data['url'])) {
        //     header("Location: {$data['url']}", true, $http_code);
        // }
        // var_dump($this->_tpl);
        $dispatcher = new Yaf\View\Simple(APP_PATH);
        $dispatcher->assign('data', $data);
        $dispatcher->display('views/public/jump.tpl');
        exit();

    }
}
