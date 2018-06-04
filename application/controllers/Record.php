<?php

class RecordController extends BaseController
{
    private $conohaDns;

    public function init()
    {
        parent::init();
        $this->checkLogin();
        $this->conohaDns = new ConohaDns($this->config->conoha->username, $this->config->conoha->password, $this->config->conoha->tenant_id);
    }

    public function infoAction()
    {
        $id     = $this->request->get('id');
        $domain = $this->request->get('domain');
        if (empty($id)) {
            $this->error('パラメータエラー');
        }
        $record_list = $this->conohaDns->recordList($id);
        if (empty($record_list)) {
            $this->error('Conohaは何のデータに戻りませんでした');
        }
        foreach ($record_list['records'] as &$val) {
            // if ($val['type'] == 'SOA') {
            //     unset($val);
            //     continue;
            // }
            $val['name'] = str_replace($domain . '.', '', $val['name']);
            if (empty($val['name'])) {
                $val['name'] = '@.';
            }
            if ($val['type'] == 'CNAME' || $val['type'] == 'MX' || $val['type'] == 'NS') {
                $val['data'] = substr($val['data'], 0, -1);
            }
        }
        unset($val);
        // var_dump($record_list);exit;
        $this->assign('r_list', $record_list);
        $this->assign('d_id', $id);
        $this->assign('domain', $domain);
        // var_dump($record_list);exit;
    }

    public function deleteAction()
    {
        $domain_id = $this->request->getPost('domain_id');
        $record_id = $this->request->getPost('record_id');
        if (empty($domain_id) || empty($record_id)) {
            $this->error('パラメータエラー' . $domain_id . $record_id);
        }

        $return = $this->conohaDns->recordDelete($domain_id, $record_id);
        if (!$return) {
            $this->error("削除失敗した({$this->conohaDns->getError()})");
        }
        $this->success('削除成功した');
    }

    public function addAction()
    {
        $allow_type = ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'PTR'];
        $domain     = $this->request->getPost('domain');
        $domain_id  = $this->request->getPost('domain_id');
        $record     = $this->request->getPost('record');
        $type       = $this->request->getPost('type');
        $ttl        = intval($this->request->getPost('ttl')) ?? 300;
        $value      = $this->request->getPost('value');
        $priority   = intval($this->request->getPost('priority')) ?? 0;
        if (empty($domain) || empty($domain_id)) {
            $this->error('パラメータエラー(ドメインまたはドメインIDが間違っています)');
        }
        // $domain .= '.'; //域名补点
        if ($record == '@') {
            $record = $domain;
        } else {
            $record .= '.' . $domain; //拼凑整个域名
        }

        if (!in_array($type, $allow_type)) {
            $this->error('パラメータエラー(タイプエラー)');
        }

        if ($type == 'MX' && empty($priority)) {
            $this->error('パラメータエラー(タイプMXの場合は優先度が必要です)');
        }

        if (($type == 'CNAME' || $type == 'NS' || $type == 'MX') && substr($value, -1) != '.') {
            $value .= '.'; //当值是域名类型且不带后缀时补点
        }

        // var_dump($priority);
        $return = $this->conohaDns->recordAdd($domain_id, $record, $type, $value, $priority, $ttl);
        if ($return) {
            $this->success('レコード作成した', '', '', $return);
        } else {
            // var_dump($domain_id, $record, $type, $value, $priority, $ttl);exit;
            $this->error("レコード作成失敗した({$this->conohaDns->getError()})");

        }
    }

    public function editAction()
    {
        $allow_type = ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'PTR'];
        $domain     = $this->request->getPost('domain');
        $domain_id  = $this->request->getPost('domain_id');
        $record_id  = $this->request->getPost('record_id');
        $record     = $this->request->getPost('record');
        $type       = $this->request->getPost('type');
        $ttl        = intval($this->request->getPost('ttl')) ?? 300;
        $value      = $this->request->getPost('value');
        $priority   = intval($this->request->getPost('priority')) ?? 0;
        if (empty($domain) || empty($domain_id) || empty($record_id)) {
            $this->error('パラメータエラー(ドメインまたはドメインIDまたはレコードIDが間違っています)');
        }
        // $domain .= '.'; //域名补点
        if ($record == '@') {
            $record = $domain;
        } else {
            $record .= '.' . $domain; //拼凑整个域名
        }

        if (!in_array($type, $allow_type)) {
            $this->error('パラメータエラー(タイプエラー)');
        }

        if ($type == 'MX' && empty($priority)) {
            $this->error('パラメータエラー(タイプMXの場合は優先度が必要です)');
        }

        if (($type == 'CNAME' || $type == 'NS' || $type == 'MX') && substr($value, -1) != '.') {
            $value .= '.'; //当值是域名类型且不带后缀时补点
        }

        $return = $this->conohaDns->recordUpdate($domain_id, $record_id, $record, '', $value, $priority, $ttl);
        if ($return) {
            $this->success('レコード更新した', '', '', $return);
        } else {
            // var_dump($domain_id, $record, $type, $value, $priority, $ttl);exit;
            $this->error("レコード更新失敗した({$this->conohaDns->getError()})");

        }

    }

    // public function testAction()
    // {
    //     var_dump($this->conohaDns->recordAdd('bc07aa7a-5b62-46ab-a0cd-98ace31349a9', 'test.rb2k.com', 'A', '8.8.8.8', 0, 302));exit;
    // }

}
