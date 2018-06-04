<?

class BaseController extends Yaf\Controller_Abstract
{
    protected $config;
    protected $reqeust;
    protected $cookie;

    public function init()
    {
        $this->config = Yaf\Application::app()->getConfig();
        $this->request = $this->_request;
        $this->cookie = new Cookie();
        $this->getView()->assign('config', $this->config);
    }

    public function assign($name, $val)
    {
        return $this->getView()->assign($name, $val);
    }

    public function checkLogin()
    {
        if (!$this->cookie->has('token')) {
            $this->error('ログインしていない', '/index/sign/in');
        } elseif ($this->cookie->get('token') !== md5($this->config->admin->user . $this->config->admin->password)) {
            $this->error('認証に失敗した', '/index/sign/in');
        }
    }

    use Jump;
}
