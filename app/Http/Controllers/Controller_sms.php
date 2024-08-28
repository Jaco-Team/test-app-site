<?php

namespace App\Http\Controllers;

class SmsClient {
    /** @var \CurlHandle */
    private $ch;

    /** @var string Ваш логин в системе */
    private $login;

    /** @var string Ваш пароль в системе */
    private $password;

    /**
     * @var array Прочие параметры авторизации
     *   - gzip
     *   - HTTP_ACCEPT_LANGUAGE
     *   - CLIENTADR
     *   - comment
     */
    private $authContext = [];

    /** @var string<array> */
    private $method = 'POST';

    /** @var string протокол */
    private $protocol = '';

    /** @var string адрес сервера отправки сообщений (без http) */
    private $hostname;

    /** @var string путь на сервере */
    private $path = '';

    /** @var string Путь к прокси-серверу, если он есть,
     * В формате <ip>:<port>
     */
    private $proxy;

    /**
     * @var string Логин и пароль к прокси-серверу, если есть.
     * В формате: <username>:<password>
     * Не имеет значения, если не заполнено свойство $proxy
     */
    private $proxyUserPwd;

    /** @var array Заголовки, отправляемые в запросе */
    private $httpHeaders = [
        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
    ];

    /** @var SmsActionInterface[] */
    private $action = [];

    /** @var mixed Хранит данные последнего выполненного запроса */
    private $responseContent;

    /** @var mixed Хранит информацию о последней ошибке */
    private $responseError;

    /** @var mixed Хранит информацию о последней операции */
    private $responseInfo;

    /** @var bool Флаг мультизапроса (несколько действий в одном запросе) */
    private $multipost = FALSE;

    /** @var array Данные, передаваемые на сервер */
    private $post_data = [];

    /**
     * Установить параметры подключения к серверу
     *
     * SmsClient constructor.
     * @param string|null $method
     * @param string|null $protocol
     * @param string|null $hostname
     * @param string|null $path
     */
    public function __construct(
        string $method = NULL,
        string $protocol = NULL,
        string $hostname = NULL,
        string $path = NULL)
    {
        $this->ch = curl_init();
        if (!empty($method)) {
            $this->method = $method;
        }
        if (!empty($protocol)) {
            $this->protocol = $protocol;
        }
        if (!empty($hostname)) {
            $this->hostname = $hostname;
        }
        if (!empty($path)) {
            $this->path = $path;
        }

        $this->connectSettings();
    }

    /**
     * Установить параметры авторизации
     *
     * @param string $login
     * @param string $password
     * @param array $context Остальные необязательные поля для авторизации
     */
    public function setAuth(string $login, string $password, array $context = [])
    {
        $this->login = $login;
        $this->password = $password;

        $this->authContext = [
            'gzip' => 'none',
            'CLIENTADR' => $_SERVER['REMOTE_ADDR'] ?? false,
            'HTTP_ACCEPT_LANGUAGE' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? false,
            'comment' => ''
        ];

        if (!empty($context)) {
            $this->formAuthContext($context);
        }
    }

    /**
     * Сформировать параметры контекста авторизации
     * @param array $context
     */
    public function formAuthContext(array $context)
    {
        $allowedFields = [
            'gzip',
            'HTTP_ACCEPT_LANGUAGE',
            'CLIENTADR',
            'comment',
        ];

        foreach ($context as $key => $value) {
            if (!in_array($key, $allowedFields)) {
                continue;
            }
            $this->authContext[$key] = $value;
        }
    }

    /**
     * @param string $proxyData Адрес прокси-сервера в виде строки "ip:port"
     */
    public function setProxy(string $proxyData)
    {
        $this->proxy = $proxyData;
        $this->setProxyParams();
    }

    /**
     * @param string $proxyData Логин/пароль к прокси-серверу в виде строки "username:password"
     */
    public function setProxyUserPwd(string $proxyData)
    {
        $this->proxyUserPwd = $proxyData;
        $this->setProxyParams();
    }

    /**
     * @param bool $verifyPeer Управлять проверкой CURL сертификата узла
     */
    public function setCurlCertificateCheck(bool $verifyPeer)
    {
        if (isset($this->ch)) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $verifyPeer);
        }
    }

    /**
     * @param string $pathToCert Указать путь к файлу сертификата (необходим для работы cURL)
     */
    public function setCurlCertificatePath(string $pathToCert)
    {
        if (isset($this->ch)) {
            curl_setopt($this->ch, CURLOPT_CAINFO, $pathToCert);
        }
    }

    /**
     * Установить action для выполнения запроса
     *
     * @param SmsActionInterface $action
     */
    public function setAction(SmsActionInterface $action)
    {
        // если не активирован режим Мультипост, то может быть только один action
        if (!$this->multipost) {
            $this->action = [];
        }
        $this->action[] = $action;
    }

    /**
     * Общие установки CURL
     */
    private function connectSettings()
    {
        $protocol = !empty($this->protocol) ? $this->protocol.'://' : '';
        $path = !empty($this->path) ? $this->path : '';
        $url = $protocol . $this->hostname . $path;

        curl_setopt_array($this->ch, [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => FALSE,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $this->buildHeaders(),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => FALSE,
            CURLOPT_USERAGENT => 'AISMS PHP class',
        ]);
    }

    /**
     * Выполнить запрос
     */
    public function sendRequest()
    {
        // собрать поля для POST-запроса
        $postFields = [
            'user' => $this->login,
            'pass' => $this->password,
            'gzip' => $this->authContext['gzip'],
            'HTTP_ACCEPT_LANGUAGE' => $this->authContext['HTTP_ACCEPT_LANGUAGE'],
            'CLIENTADR' => $this->authContext['CLIENTADR'],
            'comment' => $this->authContext['comment'],
        ];

        // Установить прочие параметры запроса (зависит от классов, реализующих SmsActionInterface)
        $postFieldsAction = NULL;
        if (!$this->multipost) {
            $postFieldsAction = $this->action[0]->formPostFields();
        } else {
            foreach ($this->action as $action) {
                $postFieldsAction['data'][] = $action->formPostFields();
            }
        }
        $postFields = array_merge($postFields, $postFieldsAction);
        $query = http_build_query($postFields, '', '&');

        if ($this->method === 'POST') {
            curl_setopt($this->ch, CURLOPT_POST, TRUE);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $query);
        }

        $this->responseContent = curl_exec($this->ch);
        $this->responseError = curl_error($this->ch);
        $this->responseInfo = curl_getinfo($this->ch);

        return $this->responseContent;
    }

    public function isMultipost()
    {
        return $this->multipost;
    }

    public function getResponseContent()
    {
        return $this->responseContent;
    }

    /**
     * @return mixed
     */
    public function getResponseError()
    {
        return $this->responseError;
    }

    /**
     * @return mixed
     */
    public function getResponseInfo()
    {
        return $this->responseInfo;
    }

    /**
     * ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
     */

    /**
     * Вернуть массив с http-заголовками, подготовленный к использованию CURL
     *
     * @return array
     */
    private function buildHeaders(): array
    {
        $headers = [];
        foreach ($this->httpHeaders as $key => $header) {
            $headers[] = $key . ': ' . $header;
        }
        return $headers;
    }

    /**
     * Установить/заменить значение заголовка
     *
     * @param string $headerName
     * @param string|null $headerValue
     */
    public function addHeader(string $headerName, string $headerValue = NULL)
    {
        $this->httpHeaders[$headerName] = $headerValue;
    }

    /**
     * Установить параметры прокси-сервера
     */
    public function setProxyParams()
    {
        if (!empty($this->proxy)) {
            curl_setopt($this->ch, CURLOPT_PROXY , $this->proxy);
            if (!empty($this->proxyUserPwd)) {
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $this->proxyUserPwd);
            }
        }
    }

    /**
     * Команда на начало мультизапроса
     */
    public function startMultipost()
    {
        $this->multipost = TRUE;
    }

}

interface SmsActionInterface {

    public function __construct();

    /**
     * Вернуть название action
     * @return string
     */
    public function getActionName(): string;

    /**
     * Установить параметры запроса
     *
     * @param array $params
     */
    public function setParams(array $params);

    /**
     * Вернуть данные для тела POST запроса
     *
     * @return array
     */
    public function formPostFields(): array;

    /**
     * Валидация параметров при установке
     *
     * @param array $params
     * @return mixed
     */
    public function validateParams(array $params);

}

abstract class SmsAction {
    const ACTION_SENDSMS = 'post_sms';
    const ACTION_STATUS = 'status';
    const ACTION_BALANCE = 'balance';
    const ACTION_INBOX = 'inbox';
    const ACTION_BLACKLIST = 'blacklist';
    const ACTION_BLACKLIST_ADD = 'blacklist_add';
    const ACTION_BLACKLIST_DELETE = 'blacklist_delete';

    /** @var string Название действия */
    protected $action;

    /** @var array Список параметров */
    protected $params = [];

    /**
     * @var array Хранит список ошибок при установке параметров
     */
    protected $errorParams = [];

    /**
     * Установить параметры
     * @param array|null $params
     * @return bool|string[]
     */
    public function setParams(array $params = NULL)
    {
        if (!$this->validateParams($params)) {
            return $this->errorParams;
        }

        if (!empty($params)) {
            foreach ($params as $key => $param) {
                if (array_key_exists($key, $this->params)) {
                    $this->params[$key] = $param;
                }
            }
        }

        return TRUE;
    }

    /**
     * Вернуть название action
     * @return string
     */
    public function getActionName(): string
    {
        return $this->action;
    }

    /**
     * Вернуть поля для POST-запроса
     *
     * @return string[]
     */
    public function formPostFields(): array
    {
        $postFields = [
            'action' => $this->getActionName()
        ];

        foreach ($this->params as $key => $param) {
            $postFields[$key] = $param;
        }

        return $postFields;
    }

    /**
     * Валидация устанавливаемых параметров
     *
     * @return bool|string[]
     */
    public function validateParams(array $params = NULL)
    {
        if (!empty($this->errorParams)) {
            return $this->errorParams;
        }
        return TRUE;
    }

}

final class SmsActionStatus extends SmsAction implements SmsActionInterface{
    protected $params = [
        'sms_id' => NULL,
        'sms_group_id' => NULL,
        'date_from' => NULL,
        'date_to' => NULL,
    ];

    public function __construct()
    {
        $this->action = self::ACTION_STATUS;
    }

    public function validateParams(array $params = NULL): bool
    {
        // пример валидатора для класса отправки СМС
        $prefix = 'validator_status';
        extract($params, EXTR_PREFIX_ALL, $prefix);

        // должен быть указан хотя бы один из параметров sms_id, sms_group_id или date_from+date_to
        if (empty(${$prefix . '_sms_id'})
            && empty(${$prefix . '_sms_group_id'})
            && (empty(${$prefix . '_date_from'}) || empty(${$prefix . '_date_to'}))
        ) {
            $this->errorParams[] = 'Должен быть указан хотя бы один из параметров: '
                . 'sms_id, sms_group_id, date_from, date_to';
        }

        if (!empty($this->errorParams)) {
            return FALSE;
        }
        return TRUE;
    }

}

final class SmsActionPostSms extends SmsAction implements SmsActionInterface{
    protected $params = [
        'message' => NULL,
        'target' => NULL,
        'phl_codename' => NULL,
        'sender' => NULL,
        'post_id' => NULL,
        'period' => NULL,
        'time_period' => NULL,
        'time_local' => NULL,
        'autotrimtext' => NULL,
        'sms_type' => NULL,
        'wap_url' => NULL,
        'wap_expires' => NULL,
    ];

    public function __construct()
    {
        $this->action = self::ACTION_SENDSMS;
    }

    public function validateParams(array $params = NULL): bool
    {
        // пример валидатора для класса отправки СМС
        $prefix = 'validator_post_sms';
        extract($params, EXTR_PREFIX_ALL, $prefix);

        if (!empty(${$prefix . '_phl_codename'}) && !empty(${$prefix . '_target'})) {
            $this->errorParams[] = 'Несовместимые параметры в одном запросе: '
                . 'phl_codename, target';
        }

        if (!empty($this->errorParams)) {
            return FALSE;
        }
        return TRUE;
    }

}

final class SmsActionInbox extends SmsAction implements SmsActionInterface{
    protected $params = [
        'sib_num' => NULL, // обязательный
        'new_only' => NULL,
        'date_from' => NULL,
        'date_to' => NULL,
    ];

    public function __construct()
    {
        $this->action = self::ACTION_INBOX;
    }

    public function validateParams(array $params = NULL): bool
    {
        $prefix = 'validator_inbox';
        extract($params, EXTR_PREFIX_ALL, $prefix);

        if (empty(${$prefix . '_sib_num'})
        ) {
            $this->errorParams[] = 'Требуется указать обязательный параметр: sib_num';
        }

        if (!empty($this->errorParams)) {
            return FALSE;
        }
        return TRUE;
    }

}

final class SmsActionBlacklistDelete extends SmsAction implements SmsActionInterface{
    protected $params = [
        'phones' => NULL, // обязательный
    ];

    public function __construct()
    {
        $this->action = self::ACTION_BLACKLIST_DELETE;
    }

    public function validateParams(array $params = NULL): bool
    {
        $prefix = 'validator_blacklist_delete';
        extract($params, EXTR_PREFIX_ALL, $prefix);

        if (empty(${$prefix . '_phones'})
        ) {
            $this->errorParams[] = 'Требуется указать обязательный параметр: phones';
        }

        if (!empty($this->errorParams)) {
            return FALSE;
        }
        return TRUE;
    }

}

final class SmsActionBlacklistAdd extends SmsAction implements SmsActionInterface{
    protected $params = [
        'phones' => NULL, // обязательный
    ];

    public function __construct()
    {
        $this->action = self::ACTION_BLACKLIST_ADD;
    }

    public function validateParams(array $params = NULL): bool
    {
        $prefix = 'validator_blacklist_add';
        extract($params, EXTR_PREFIX_ALL, $prefix);

        if (empty(${$prefix . '_phones'})
        ) {
            $this->errorParams[] = 'Требуется указать обязательный параметр: phones';
        }

        if (!empty($this->errorParams)) {
            return FALSE;
        }
        return TRUE;
    }

}

final class SmsActionBlacklist extends SmsAction implements SmsActionInterface{
    protected $params = [
        'perp' => NULL,
        'page' => NULL,
        'search' => NULL,
    ];

    public function __construct()
    {
        $this->action = self::ACTION_BLACKLIST;
    }

}

final class SmsActionBalance extends SmsAction implements SmsActionInterface{
    public function __construct()
    {
        $this->action = self::ACTION_BALANCE;
    }

}

class QTSMS{
    /** @var SmsClient */
    private $smsClient;

    /** @var string Путь к файлу с сертификатом по-умолчанию */
    private $pathToCertPem = './Src/cacert.pem';

    public function __construct(string $user, string $password, string $host){
        $this->smsClient = new SmsClient(
            'POST',
            '',
            $host
        );
        $this->smsClient->setAuth($user, $password);

        // по-умолчанию проверка сертификата узла для CURL отключена
        $this->smsClient->setCurlCertificateCheck(FALSE);
        // указать CURL путь к файлу с сертификатом (если требуется включить проверку)
        // см. https://curl.se/docs/caextract.html
        // $realpath = realpath(__DIR__ . $this->pathToCertPem);
        // $this->smsClient->setCurlCertificatePath($realpath);

        // установите параметры прокси-сервера, если он у вас есть
        // $this->set_proxy('<ip>:<port>');
    }

    /**
     * Установить параметры прокси-сервера
     * @param string $proxyData Адрес прокси-сервера в виде "ip:port"
     */
    public function set_proxy(string $proxyData){
        $this->smsClient->setProxy($proxyData);
    }

    /**
     * Установить параметры прокси-сервера
     * @param string $proxyData Логин/пароль к прокси-серверу в виде "username:password"
     */
    public function set_proxy_user_pwd(string $proxyData){
        $this->smsClient->setProxyUserPwd($proxyData);
    }

    /**
     * Работа с методами старого интерфейса
     */
    public function start_multipost(){
        $this->smsClient->startMultipost();
    }

    public function process(){
        return $this->smsClient->sendRequest();
    }

    /****************************************
     ***        отправка сообщений        ***
     ****************************************/

    public function post_mes(
        string $mes,
        string $target,
        string $phl_codename,
        string $sender,
        string $post_id=NULL,
        string $period=NULL)
    {
        $action = new SmsActionPostSms();
        $action->setParams([
            'message' => $mes,
            'target' => $target ?? NULL,
            'phl_codename' => $phl_codename ?? NULL,
            'sender' => $sender,
            'post_id' => $post_id,
            'period' => $period,
        ]);
        $this->smsClient->setAction($action);
        if (!$this->smsClient->isMultipost()) {
            return $this->smsClient->sendRequest();
        }
    }

    public function post_message($mes, $target, $sender = NULL, $post_id = NULL, $period = FALSE){
        if (is_array($target)) {
            $target = implode(',', $target);
        }
        return $this->post_mes($mes, $target, FALSE, $sender, $post_id, $period);
    }

    public function post_message_phl($mes, $phl_codename, $sender = NULL, $post_id = NULL, $period = FALSE){
        return $this->post_mes($mes, FALSE, $phl_codename, $sender, $post_id, $period);
    }

    /****************************************
     ***         статус сообщений         ***
     ****************************************/

    public function status_sms(
        string $date_from,
        string $date_to,
        string $smstype,
        string $sms_group_id,
        string $sms_id)
    {
        $action = new SmsActionStatus();
        $action->setParams([
            'date_from' => $date_from,
            'date_to' => $date_to,
            'smstype' => $smstype,
            'sms_group_id' => $sms_group_id,
            'sms_id' => $sms_id,
        ]);
        $this->smsClient->setAction($action);
        if (!$this->smsClient->isMultipost()) {
            return $this->smsClient->sendRequest();
        }
    }

    public function status_sms_id($sms_id){
        return $this->status_sms(FALSE, FALSE, FALSE, FALSE, $sms_id);
    }

    public function status_sms_group_id($sms_group_id){
        return $this->status_sms(FALSE, FALSE, FALSE, $sms_group_id, FALSE);
    }

    public function status_sms_date($date_from, $date_to, $smstype = 'SENDSMS'){
        return $this->status_sms($date_from, $date_to, $smstype, FALSE, FALSE);
    }

    /****************************************
     ***         проверка баланса         ***
     ****************************************/

    public function get_balance(){
        $action = new SmsActionBalance();
        $this->smsClient->setAction($action);
        if (!$this->smsClient->isMultipost()) {
            return $this->smsClient->sendRequest();
        }
    }

    /****************************************
     ***        получение входящих        ***
     ****************************************/

    public function inbox_sms(
        $new_only = FALSE,
        $sib_num = FALSE,
        $date_from = FALSE,
        $date_to = FALSE,
        $phone = FALSE,
        $prefix = FALSE)
    {
        $action = new SmsActionInbox();
        $action->setParams([
            'new_only' => $new_only ?? NULL,
            'sib_num' => $sib_num ?? NULL,
            'date_from' => $date_from ?? NULL,
            'date_to' => $date_to ?? NULL,
            'phone' => $phone ?? NULL,
            'prefix' => $prefix ?? NULL,
        ]);
        $this->smsClient->setAction($action);
        if (!$this->smsClient->isMultipost()) {
            return $this->smsClient->sendRequest();
        }
    }

}

class iqsms_JsonGate {

    const ERROR_EMPTY_API_LOGIN = 'Empty api login not allowed';
    const ERROR_EMPTY_API_PASSWORD = 'Empty api password not allowed';
    const ERROR_EMPTY_RESPONSE = 'errorEmptyResponse';

    protected $_apiLogin = null;

    protected $_apiPassword = null;

    protected $_host = 'json.gate.iqsms.ru';

    protected $_packetSize = 200;

    protected $_results = array();

    public function __construct($apiLogin, $apiPassword){
        $this->_setApiLogin($apiLogin);
        $this->_setApiPassword($apiPassword);
    }

    private function _setApiLogin($apiLogin){
        if (empty($apiLogin)) {
            throw new Exception(self::ERROR_EMPTY_API_LOGIN);
        }
        $this->_apiLogin = $apiLogin;
    }

    private function _setApiPassword($apiPassword){
        if (empty($apiPassword)) {
            throw new Exception(self::ERROR_EMPTY_API_PASSWORD);
        }
        $this->_apiPassword = $apiPassword;
    }

    public function setHost($host){
        $this->_host = $host;
    }

    public function getHost(){
        return $this->_host;
    }

    private function _sendRequest($uri, $params = null){
        $url = $this->_getUrl($uri);
        $data = $this->_formPacket($params);

        $client = curl_init($url);
        curl_setopt_array($client, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array('Host: ' . $this->getHost()),
            CURLOPT_POSTFIELDS => $data,
        ));

        $body = curl_exec($client);
        curl_close($client);
        if (empty($body)) {
            throw new Exception(self::ERROR_EMPTY_RESPONSE);
        }
        $decodedBody = json_decode($body, true);
        if (is_null($decodedBody)) {
            throw new Exception($body);
        }
        return $decodedBody;
    }

    private function _getUrl($uri){
        return 'http://' . $this->getHost() . '/' . $uri . '/';
    }

    private function _formPacket($params = null){
        $params['login'] = $this->_apiLogin;
        $params['password'] = $this->_apiPassword;
        foreach ($params as $key => $value) {
            if (empty($value)) {
                unset($params[$key]);
            }
        }
        $packet = json_encode($params);
        return $packet;
    }

    public function getPacketSize(){
        return $this->_packetSize;
    }

    public function send($messages, $statusQueueName = null, $scheduleTime = null){
        $params = array(
            'messages' => $messages,
            'statusQueueName' => $statusQueueName,
            'scheduleTime' => $scheduleTime,
        );
        return $this->_sendRequest('send', $params);
    }

    public function status($messages){
        return $this->_sendRequest('status', array('messages' => $messages));
    }

    public function statusQueue($name, $limit){
        return $this->_sendRequest('statusQueue', array(
            'statusQueueName' => $name,
            'statusQueueLimit' => $limit,
        ));
    }

    public function credits(){
        return $this->_sendRequest('credits');
    }

    public function senders(){
        return $this->_sendRequest('senders');
    }

}

class BEELINE_SMS {
    public function sendSMS($number, $text){
        $cfg = array(
            'login' => '1706431',					// ваш логин в системе
            'password' => 'Marusiaqwerti4815*',				// ваш пароль
            'host' => 'https://a2p-sms-https.beeline.ru/proto/http/'		// хост для доступа к сервису
        );

        $sms = new QTSMS($cfg['login'], $cfg['password'], $cfg['host']);

        $sms_text 		= $text;
        $sender_name 	= 'jacofood';
        $period 		= 120;
        $phone 			= $number;
        $post_id 		= 'x124127456';

        $result_sms = $sms->post_message($sms_text, $phone, $sender_name, $post_id, $period);
        $feed = simplexml_load_string($result_sms);
        $res = json_decode( json_encode( $feed ), true );
        $sms_id = $res['result']['sms']['@attributes']['id'];

        sleep(2);

        $status_sms = $sms->status_sms_id($sms_id);
        $feed = simplexml_load_string($status_sms);
        $res = json_decode( json_encode( $feed ), true );

        $code = $res['MESSAGES']['MESSAGE']['SMSSTC_CODE'];
        $text = $res['MESSAGES']['MESSAGE']['SMS_STATUS'];

        //true
        //queued / accepted / delivered

        //false
        //rejected / undeliverable / error / expired / unknown / aborted

        if( $code == 'queued' || $code == 'accepted' || $code == 'delivered' ){
            return true;
        }else{
            return false;
        }
    }

    public function getStatusSMS($sms_id){
        $sms = new QTSMS($cfg['login'], $cfg['password'], $cfg['host']);

        $status_sms = $sms->status_sms_id($sms_id);
        $feed = simplexml_load_string($status_sms);
        $res = json_decode( json_encode( $feed ), true );

        $code = $res['MESSAGES']['MESSAGE']['SMSSTC_CODE'];
        $text = $res['MESSAGES']['MESSAGE']['SMS_STATUS'];

        //true
        //queued / accepted / delivered

        //false
        //rejected / undeliverable / error / expired / unknown / aborted
    }
}

class TERRA{
    public function curl($url){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);
        curl_close($ch);

        if(empty($output)){
            return false;
        }

        return $output;
    }

    public function sendSMS($phone, $code){

        $url 		= 'https://auth.terasms.ru/outbox/send';
        $token 		= "vCxzw5DFFER0afLdEPcb";
        $login	 	= 'jacofood';
        $sender 	= 'jacofood';

        $params = array(
            'login' 	=> $login,
            'message' 	=> $code,
            'sender' 	=> $sender,
            'target' 	=> $phone,
            'type'		=> 'callpass',
        );

        ksort($params);
        $params_str = '';
        foreach($params as $i => $item){
            $params_str .= $i.'='.$item;
        }

        // формируем строку сигнатуры
        $sign				= md5($params_str.$token);
        $params['sign'] 	= $sign;
        $url 				= $url .'?'. http_build_query($params);
        $result 			= $this->curl($url);

        if($result > 0){
            return true;
        }

        return false;
    }
}

class Controller_sms extends Controller
{
    public function send_sms(string $number, string $text){

        $res = (new BEELINE_SMS())->sendSMS($number, $text);

        if( !$res || $res === false ){
            $messages = array(
                array(
                    "phone" => $number,
                    "text" => $text,
                    "sender" => "jacofood"
                )
            );

            $res = (new iqsms_JsonGate('z1516881568593', '566487'))->send($messages);

            $res = $res['messages'][0]['status'] == 'accepted' ? true : false;
        }else{
            return [
                'st' => true
            ];
        }

        return [
            'st' => $res
        ];
    }
}
