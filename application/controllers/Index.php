<?php

class IndexController extends BaseController
{

    private $conohaDns;

    public function init()
    {
        parent::init();
        $this->checkLogin();
        $this->conohaDns = new ConohaDns($this->config->conoha->username, $this->config->conoha->password, $this->config->conoha->tenant_id);
    }

    public function indexAction()
    {
        $domain      = $this->request->get('domain') ?? null;
        $domain_list = $this->conohaDns->domainList($domain);
        if (empty($domain_list)) {
            $this->error('Conohaは何のデータに戻りませんでした');
        }
        $this->assign('d_list', $domain_list);
    }

    public function addAction()
    {
        $domain = $this->request->getPost('domain');
        $ttl = intval($this->request->getPost('ttl')) ?? 300;
        $email = $this->request->getPost('email');

        if(empty($domain) || empty($email)){
            $this->error('ドメインまたはポストエラー');
        }
        $return = $this->conohaDns->domainCreate($domain,$ttl,$email);
        if(!$return){
            $this->error("追加失敗した({$this->conohaDns->getError()})");
        }
        $this->success('追加成功した');
    }

    public function testAction()
    {
        $conoha_dns = new ConohaDns($this->config->conoha->username, $this->config->conoha->password, $this->config->conoha->tenant_id);
        // var_dump($conoha_dns->domainList());
        // $domain = $conoha_dns->domainCreate('rb2k.com', 3600, 'q844268235q@msn.com', '', 0);

        // var_dump($conoha_dns->recordUpdate('bc07aa7a-5b62-46ab-a0cd-98ace31349a9', 'c7e6acbc-039f-46b4-854d-816e7c5ab6a7', 'test2.rb2k.com', 'a', '1.2.3.4'), $conoha_dns->getError('code'));
        var_dump($conoha_dns->recordList('bc07aa7a-5b62-46ab-a0cd-98ace31349a9'));
        // var_dump($conoha_dns->domainUpdate('bc07aa7a-5b62-46ab-a0cd-98ace31349a9', 300));

        exit();
        // var_dump((new Yaf\Request\Http())->isXmlHttpRequest());
        // $this->error('嗝屁了');
    }
}
