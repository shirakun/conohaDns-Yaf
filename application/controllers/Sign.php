<?php

class SignController extends BaseController
{
    public function indexAction()
    {

    }

    public function inAction()
    {
        if ($this->request->isPost()) {
            $token = md5($this->request->getPost('username') . $this->request->getPost('password'));
            $user  = md5($this->config->admin->user . $this->config->admin->password);
            // var_dump($token, $user);
            if ($token !== $user) {
                $this->error('ユーザ名またはパスワードのエラー');
            }
            $this->cookie->set('token', $token);
            // exit;
            $this->success('ログイン成功した', '/?'.time());
        }
    }

    public function outAction()
    {

    }

    // public function logAction()
    // {

    // }
}
