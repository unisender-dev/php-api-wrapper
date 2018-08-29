<?php

namespace Unisender\ApiWrapper;

/**
 * API UniSender.
 *
 * @link https://www.unisender.com/en/support/integration/api/
 * @link https://www.unisender.com/ru/support/integration/api/
 *
 * @method sendSms(array $params) Отправить SMS-сообщение
 * @method sendEmail(array $params) Упрощённая отправка индивидуальных email-сообщений
 * @method getLists() Получить списки рассылки с их кодами
 * @method createList(array $params) Создать новый список рассылки
 * @method updateList(array $params) Изменить свойства списка рассылки
 * @method deleteList(array $params) Удалить список рассылки
 * @method exclude(array $params) Исключить адресата из списков рассылки
 * @method unsubscribe(array $params) Отписать адресата от рассылки
 * @method importContacts(array $params) Массовый импорт и синхронизация контактов
 * @method exportContacts(array $params = array()) Экспорт всех данных контактов
 * @method getTotalContactsCount(array $params) Получить размер базы пользователя
 * @method getContactCount(array $params) Получить количество контактов в списке
 * @method createEmailMessage(array $params) Создать e-mail для массовой рассылки
 * @method createSmsMessage(array $params) Создать SMS для массовой рассылки
 * @method createCampaign(array $params) Запланировать массовую отправку e-mail или SMS сообщения
 * @method getActualMessageVersion(array $params) Получить актуальную версию письма
 * @method checkSms(array $params) Проверить статус доставки SMS
 * @method sendTestEmail(array $params) Отправка тестовых email-сообщений (на собственный адрес)
 * @method checkEmail(array $params) Проверить статус доставки email
 * @method updateOptInEmail(array $params) Изменить текст письма со ссылкой подтверждения подписки
 * @method getWebVersion(array $params) Получить ссылку на веб-версию отправленного письма
 * @method deleteMessage(array $params) Удалить сообщение
 * @method createEmailTemplate(array $params) Создать шаблон сообщения для массовой рассылки
 * @method updateEmailTemplate(array $params) Редактировать существующий шаблон сообщения
 * @method deleteTemplate(array $params) Удалить шаблон
 * @method getTemplate(array $params) Получение информации о шаблоне
 * @method getTemplates(array $params = array()) Получить список всех шаблонов, созданных в системе
 * @method listTemplates(array $params = array()) Получить список всех шаблонов без body
 * @method getCampaignDeliveryStats(array $params) Получить отчёт о статусах доставки сообщений для заданной рассылки
 * @method getCampaignCommonStats(array $params) Получить общие сведения о результатах доставки для заданной рассылки
 * @method getVisitedLinks(array $params) Получить статистику переходов по ссылкам
 * @method getCampaigns(array $params = array()) Получить список рассылок
 * @method getCampaignStatus(array $params) Получить статус рассылки
 * @method getMessages(array $params = array()) Получить список сообщений
 * @method getMessage(array $params) Получение информации об SMS или email сообщении
 * @method listMessages(array $params) Получить список сообщений без тела и вложений
 * @method getFields() Получить список пользовательских полей
 * @method createField(array $params) Создать новое поле
 * @method updateField(array $params) Изменить параметры поля
 * @method deleteField(array $params) Удалить поле
 * @method getTags() Получить список пользовательских меток
 * @method deleteTag(array $params) Удалить метку
 */
class UnisenderApi
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * @var int
     */
    protected $retryCount = 0;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * @var bool
     */
    protected $compression = false;

    /**
     *
     * @var string
     */
    protected $platform = '';

    /**
     * UniSender Api constructor
     *
     * For example:
     *
     * <pre>
     *
     * $platform = 'My E-commerce product v1.0';
     *
     * $UnisenderApi = new UnisenderApi('api key here', 'UTF-8', 4, null, false, $platform);
     * $UnisenderApi->sendSms(
     *      ['phone' => 380971112233, 'sender' => 'SenderName', 'text' => 'Hello World!']
     * );
     *
     * </pre>
     *
     * @param string $apiKey        Provide your api key here.
     * @param string $encoding      If your current encoding is different from UTF-8, specify it here.
     * @param int    $retryCount
     * @param int    $timeout
     * @param bool   $compression
     * @param string $platform      Specify your product name, example - My E-commerce v1.0.
     *
     */
    public function __construct($apiKey, $encoding = 'UTF-8', $retryCount = 4, $timeout = null, $compression = false, $platform = null)
    {
        $this->apiKey = $apiKey;
        $platform = trim((string) $platform);

        if (!empty($encoding)) {
            $this->encoding = $encoding;
        }

        if (0 < $retryCount) {
            $this->retryCount = $retryCount;
        }

        if (null !== $timeout) {
            $this->timeout = $timeout;
        }

        if ($compression) {
            $this->compression = $compression;
        }

        if ($platform !== '') {
            $this->platform = $platform;
        }
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return string
     */
    public function __call($name, $arguments)
    {
        if (!is_array($arguments) || 0 === count($arguments)) {
            $params = [];
        } else {
            $params = $arguments[0];
        }

        return $this->callMethod($name, $params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function subscribe($params)
    {
        $params = (array) $params;

        if (empty($params['request_ip'])) {
            $params['request_ip'] = $this->getClientIp();
        }

        return $this->callMethod('subscribe', $params);
    }

    /**
     * @param string $json
     *
     * @return mixed
     */
    protected function decodeJSON($json)
    {
        return json_decode($json);
    }

    /**
     * @return string
     */
    protected function getClientIp()
    {
        $result = '';

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $result = $_SERVER['REMOTE_ADDR'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $result = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $result = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (preg_match('/([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])){3}/', $result, $match)) {
            return $match[0];
        }

        return $result;
    }

    /**
     * @param string $value
     * @param string $key
     */
    protected function iconv(&$value, $key)
    {
        $value = iconv($this->encoding, 'UTF-8//IGNORE', $value);
    }

    /**
     * @param string $value
     * @param string $key
     */
    protected function mb_convert_encoding(&$value, $key)
    {
        $value = mb_convert_encoding($value, 'UTF-8', $this->encoding);
    }

    /**
     * @param string $methodName
     * @param array  $params
     *
     * @return string|bool
     */
    protected function callMethod($methodName, $params = [])
    {
        if ($this->platform !== '') {
            $params['platform'] = $this->platform;
        }

        if (strtoupper($this->encoding) !== 'UTF-8') {
            if (function_exists('iconv')) {
                array_walk_recursive($params, [$this, 'iconv']);
            } elseif (function_exists('mb_convert_encoding')) {
                array_walk_recursive($params, [$this, 'mb_convert_encoding']);
            }
        }

        $url = $methodName.'?format=json';

        if ($this->compression) {
            $url .= '&api_key='.$this->apiKey.'&request_compression=bzip2';
            $content = bzcompress(http_build_query($params));
        } else {
            $params = array_merge((array) $params, ['api_key' => $this->apiKey]);
            $content = http_build_query($params);
        }

        $contextOptions = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $content,
            ],
            'ssl' => [
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
            ]
        ];

        if ($this->timeout) {
            $contextOptions['http']['timeout'] = $this->timeout;
        }

        $retryCount = 0;
        $context = stream_context_create($contextOptions);

        do {
            $host = $this->getApiHost();
            $result = @file_get_contents($host.$url, false, $context);
            ++$retryCount;
        } while ($result === false && $retryCount < $this->retryCount);

        return $result;
    }

    /**
     * @return string
     */
    protected function getApiHost()
    {
        return 'https://api.unisender.com/en/api/';
    }
}
