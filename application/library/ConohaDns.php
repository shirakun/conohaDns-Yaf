<?php

class ConohaDns
{
    //認証サーバーのAPIエンドポイント
    protected $identityUrl = 'https://identity.tyo1.conoha.io/v2.0/tokens';

    //DNSのAPIエンドポイント
    protected $apiUrl = 'https://dns-service.tyo1.conoha.io/v1';

    protected $apiVer = 'v1';

    protected $baseUrl = '';

    protected $header = [
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    ];

    protected $cachePath;

    protected $tokenFile;

    protected $token = '';

    private $_errorCode = 0;

    private $_errorMsg = '';

    private $_abnormal = false;

    private $_errMsgList = [
        -405 => 'Authentication failure',
        -1   => 'No return',
        400  => 'Invalid Object',
        401  => 'Access Denied',
        404  => 'Domain Not Found',
        405  => 'Duplicate Domain',
    ];

    public function __construct(string $username, string $password, string $tenant_id)
    {
        $this->cachePath = (empty(ini_get('upload_tmp_dir')) ? '/tmp' : ini_get('upload_tmp_dir')) . '/' .md5($username.$tenant_id);
        $this->tokenFile = $this->cachePath . '/token_info';
        $this->setToken($username, $password, $tenant_id);
        $this->header['X-Auth-Token'] = $this->token;
    }

    /**
     * 获取域名列表或域名信息
     * @param string $name 指定要获取的域名
     */
    public function domainList($name = null)
    {
        $url = $this->apiUrl . '/domains';
        if (!empty($name)) {
            $url .= '?' . http_build_query(['name' => $name]);
        }
        $response = $this->request($url, 'get');
        return $this->dataCkeck($response);
    }

    /**
     * 获取域名的ns记录
     * @param string $uuid 域名的id
     */
    public function domainServers($uuid)
    {
        $url      = $this->apiUrl . "/domains/{$uuid}/servers";
        $response = $this->request($url, 'get');
        return $this->dataCkeck($response);
    }

    /**
     * 创建新域名
     * @param string $name 域名
     * @param int $ttl ttl(秒),取值范围60～2147483647
     * @param string $email 指定邮箱
     * @param string $description 备注
     * @param int @gslb 是否开启gslb功能
     */
    public function domainCreate(string $name, int $ttl = 300, string $email, string $description = '', int $gslb = 0)
    {
        $url  = $this->apiUrl . '/domains';
        $data = array_filter([
            'name'        => $name . '.',
            'ttl'         => $ttl,
            'email'       => $email,
            'description' => $description,
            'gslb'        => $gslb,
        ]);
        $data     = json_encode($data, JSON_UNESCAPED_UNICODE);
        $response = $this->request($url, 'post', $data);
        return $this->dataCkeck($response);

    }

    /**
     * 域名信息更新
     * @param string $uuid 域名id
     * @param int $ttl ttl(秒),取值范围60～2147483647
     * @param string $email 指定邮箱
     * @param string $description 备注
     * @param int @gslb 是否开启gslb功能
     */
    public function domainUpdate(string $uuid, int $ttl = 300, string $email = '', string $description = '', int $gslb = 0)
    {
        $url  = $this->apiUrl . '/domains/' . $uuid;
        $data = array_filter([
            // 'name'        => $name . '.',
            'ttl'         => $ttl,
            'email'       => $email,
            'description' => $description,
            'gslb'        => $gslb,
        ]);
        $data     = json_encode($data, JSON_UNESCAPED_UNICODE);
        $response = $this->request($url, 'put', $data);
        return $this->dataCkeck($response);

    }

    /**
     * 删除已经创建的域名
     * @param string $uuid 域名id
     */
    public function domainDelete(string $uuid)
    {
        $url      = $this->apiUrl . '/domains/' . $uuid;
        $response = $this->request($url, 'delete');
        return $this->dataCkeck($response);
    }

    /**
     * 域名详细信息
     * @param string $uuid 域名id
     */
    public function domainInfo(string $uuid)
    {
        $url      = $this->apiUrl . '/domains/' . $uuid;
        $response = $this->request($url, 'get');
        return $this->dataCkeck($response);
    }

    /**
     * 域名解析列表
     * @param string $uuid 域名id
     */
    public function recordList(string $uuid)
    {
        $url      = $this->apiUrl . '/domains/' . $uuid . '/records';
        $response = $this->request($url, 'get');
        return $this->dataCkeck($response);
    }

    /**
     * 添加解析记录
     * @param string $uuid 域名id
     * @param string $name 域名的子记录(包含域名本身)
     * @param string $type 解析类型[A/AAAA/MX/CNAME/TXT/SRV/NS/PTR]
     * @param string $data 解析值
     * @param int $priority 优先级 MX/SRV必须
     * @param int $ttl ttl(秒)
     * @param string $description 备注
     * @param string $gslb_region gslb地区[JP/US/SG/AUTO(自動割当)]
     * @param int $gslb_weight gslb优先级[0～255]
     * @param int $gslb_check glsb检测端口 [0:OFF/PortNo.] GSLBヘルスチェックポート、GSLBレコードの場合gslb_region、weight、checkいずれかを入力
     */
    public function recordAdd(string $uuid, string $name, string $type, string $data, int $priority = 0, int $ttl = 300, string $description = '', string $gslb_region = '', int $gslb_weight = 0, int $gslb_check = 0)
    {
        $url  = $this->apiUrl . '/domains/' . $uuid . '/records';
        $data = array_filter([
            'name'        => $name . '.',
            'type'        => strtoupper($type),
            'data'        => $data,
            'priority'    => $priority,
            'ttl'         => $ttl,
            'description' => $description,
            'gslb_region' => strtoupper($gslb_region),
            'gslb_weight' => $gslb_weight,
            'gslb_check'  => $gslb_check,
        ]);
        $data     = json_encode($data, JSON_UNESCAPED_UNICODE);
        $response = $this->request($url, 'post', $data);
        return $this->dataCkeck($response);
    }

    /**
     * 更新解析记录
     * @param string $uuid 域名id
     * @param string $record_id 域名id
     * @param string $name 域名的子记录(包含域名本身)
     * @param string $type 解析类型[A/AAAA/MX/CNAME/TXT/SRV/NS/PTR]
     * @param string $data 解析值
     * @param int $priority 优先级 MX/SRV必须
     * @param int $ttl ttl(秒)
     * @param string $description 备注
     * @param string $gslb_region gslb地区[JP/US/SG/AUTO(自動割当)]
     * @param int $gslb_weight gslb优先级[0～255]
     * @param int $gslb_check glsb检测端口 [0:OFF/PortNo.] GSLBヘルスチェックポート、GSLBレコードの場合gslb_region、weight、checkいずれかを入力
     */
    public function recordUpdate(string $uuid, string $record_id, string $name = '', string $type = '', string $data = '', int $priority = 0, int $ttl = 300, string $description = '', string $gslb_region = '', int $gslb_weight = 0, int $gslb_check = 0)
    {
        $url  = $this->apiUrl . '/domains/' . $uuid . '/records/' . $record_id;
        $data = array_filter([
            // 'record_id'   => $record_id,
            'name'        => $name . '.',
            'type'        => strtoupper($type),
            'data'        => $data,
            'priority'    => $priority,
            'ttl'         => $ttl,
            'description' => $description,
            'gslb_region' => strtoupper($gslb_region),
            'gslb_weight' => $gslb_weight,
            'gslb_check'  => $gslb_check,
        ]);
        $data     = json_encode($data, JSON_UNESCAPED_UNICODE);
        $response = $this->request($url, 'put', $data);
        return $this->dataCkeck($response);
    }

    /**
     * 域名删除
     * @param string $uuid 域名id
     * @param string $record_id 记录id
     */
    public function recordDelete(string $uuid, $record_id)
    {
        $url      = $this->apiUrl . '/domains/' . $uuid . '/records/' . $record_id;
        $response = $this->request($url, 'delete');
        return $this->dataCkeck($response);
    }

    /**
     * 域名信息
     * @param string $uuid 域名id
     * @param string $record_id 记录id
     */
    public function recordInfo(string $uuid, $record_id)
    {
        $url      = $this->apiUrl . '/domains/' . $uuid . '/records/' . $record_id;
        $response = $this->request($url, 'get');
        return $this->dataCkeck($response);
    }

    public function getError($type = 'code')
    {
        if ($type = 'code') {
            return $this->_errorCode;
        }
        if ($type = 'msg') {
            return $this->_errorMsg;
        }
        return false;
    }
    /**
     * 检查token是否过期和设置token
     *
     */
    private function setToken(string $username, string $password, string $tenant_id)
    {
        if (!is_dir($this->cachePath)) {
            $oldumask=umask(0); 
            mkdir($this->cachePath,0777);
            umask($oldumask);
        }
        $token_info = false;
        if (file_exists($this->tokenFile)) {
            $token_info = unserialize(file_get_contents($this->tokenFile));
            if ($token_info['expire'] < time()) {
                $token_info = false;
            }
        }
        if (!$token_info) {
            $token_info  = $this->token  = $this->getToken($username, $password, $tenant_id);
            $this->token = $token_info['token'];
            file_put_contents($this->tokenFile, serialize($token_info));
        } else {
            $this->token = $token_info['token'];
        }
    }

    /**
     * 获取token信息
     */
    private function getToken(string $username, string $password, string $tenant_id)
    {
        $auth = [
            'auth' => [
                'passwordCredentials' => [
                    'username' => $username,
                    'password' => $password,
                ],
                'tenantId'            => $tenant_id,
            ],
        ];
        $auth     = json_encode($auth, JSON_UNESCAPED_UNICODE);
        $response = $this->request($this->identityUrl, 'post', $auth);
        if (!empty($response) && $response['code'] == 405) {
            $response['code'] = -405;
        }
        // $response = json_decode($response['body'], true);
        $response = $this->dataCkeck($response);
        $data     = [
            'token'     => $response['access']['token']['id'],
            'audit_ids' => $response['access']['token']['audit_ids'],
            'expire'    => strtotime($response['access']['token']['expires']) - 60, //防止在请求时token过期
        ];

        return $data;
    }

    private function request($url = '', $method = 'post', $param = '')
    {

        foreach ($this->header as $k => $v) {
            $header[] = "{$k}: {$v}";
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $header,
        ]);

        if (!empty($param)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
        }

        $data['body'] = curl_exec($curl);
        $data['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $data;
    }

    private function dataCkeck($response)
    {
        if (empty($response)) {
            $response['code'] = -1;
        }

        if ($response['code'] != 200) {
            $this->_errorCode = $response['code'];
            if (isset($this->_errMsgList[$response['code']])) {

                $this->_errorMsg = $this->_errMsgList[$response['code']];
            } else {
                $this->_errorMsg = "Unknown error({$response['code']})";
            }
            if ($this->_abnormal == true) {
                throw new Exception($this->_errorMsg);
            }
            return false;
        }
        if ($response['code'] == 200 && $response['body'] === "") {
            $response['body'] = '"ok"';
        }

        return json_decode($response['body'], true);
    }

}
