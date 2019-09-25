<?php

namespace Unisender\ApiWrapper;

/**
 * API UniSender.
 *
 * @link https://www.unisender.com/en/support/integration/api/
 * @link https://www.unisender.com/ru/support/integration/api/
 *
 * @method sendSms(array $params) It is a method for easy sending the one SMS to one or several recipients.
 * @method sendEmail(array $params) It is a method to send a single individual email without personalization and
 * with limited possibilities to obtain statistics. To send transactional letters, use the
 * UniOne — the transactional letter service from UniSender. https://www.unisender.com/en/features/unione/
 * @method getLists() It is a method to get the list of all available campaign lists.
 * @method createList(array $params) It is a method to create a new contact list.
 * @method updateList(array $params) It is a method to change campaign list properties.
 * @method deleteList(array $params) It is a method to delete a list.
 * @method exclude(array $params) The method excludes the contact’s email or phone number from one or several lists.
 * @method unsubscribe(array $params) The method unsubscribes the contact email or phone number from one or several
 * lists.
 * @method importContacts(array $params) It is a method of bulk import of contacts.
 * @method getTotalContactsCount(array $params) The method returns the contacts database size by the user login.
 * @method getContactCount(array $params) Get contact count in list.
 * @method createEmailMessage(array $params) It is a method to create an email without sending it.
 * @method createSmsMessage(array $params) It is a method to create SMS messages without sending them.
 * @method createCampaign(array $params) This method is used to schedule or immediately start sending email
 * or SMS messages.
 * @method getActualMessageVersion(array $params) The method returns the id of the relevant version of
 * the specified letter.
 * @method checkSms(array $params) It returns a string — the SMS sending status.
 * @method sendTestEmail(array $params) It is a method to send a test email message.
 * @method checkEmail(array $params) The method allows you to check the delivery status of emails sent
 * using the sendEmail method.
 * @method updateOptInEmail(array $params) Each campaign list has the attached text of the invitation
 * to subscribe and confirm the email that is sent to the contact to confirm the campaign. The text of the letter
 * can be changed using the updateOptInEmail method.
 * @method getWebVersion(array $params) It is a method to get the link to the web version of the letter.
 * @method deleteMessage(array $params) It is a method to delete a message.
 * @method createEmailTemplate(array $params) It is a method to create an email template for a mass campaign.
 * @method updateEmailTemplate(array $params) It is a method to edit email templates for a mass campaign.
 * @method deleteTemplate(array $params) It is a method to delete a template.
 * @method getTemplate(array $params) The method returns information about the specified template.
 * @method getTemplates(array $params = []) This method is used to get the list of templates created
 * both through the UniSender personal account and through the API.
 * @method listTemplates(array $params = []) This method is used to get the list of templates created both
 * through the UniSender personal account and through the API.
 * @method getCampaignCommonStats(array $params) The method returns statistics similar to «Campaigns».
 * @method getVisitedLinks(array $params) Get a report on the links visited by users in the specified email campaign.
 * @method getCampaigns(array $params = array()) It is a method to get the list of all available campaigns.
 * @method getCampaignStatus(array $params) Find out the status of the campaign created using the createCampaign method.
 * @method getMessages(array $params = []) This method is used to get the list of letters created both
 * through the UniSender personal account and through the API.
 * @method getMessage(array $params) It is a method to get information about SMS or email message.
 * @method listMessages(array $params) This method is used to get the list of messages created both through
 * the UniSender personal account and through the API. The method works like getMessages, the difference of
 * listMessages is that the letter body and attachments are not returned, while the user login is returned. To get the
 * body and attachments, use the getMessage method.
 * @method getFields() It is a method to get the list of user fields.
 * @method createField(array $params) It is a method to create a new user field, the value of which can be set for
 * each recipient, and then it can be substituted in the letter.
 * @method updateField(array $params) It is a method to change user field parameters.
 * @method deleteField(array $params) It is a method to delete a user field.
 * @method getTags() It is a method to get list of all tags.
 * @method deleteTag(array $params) It is a method to delete a user tag.
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
    protected $apiHost = 'https://api.unisender.com/en/api/';

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
     * Export of contact data from UniSender.
     * Depending on the number of contacts to export, the file may take some time to prepare.
     * After the export is ready, it will be sent to the URL specified in the notify_url parameter of
     * the async/exportContacts method.
     *
     * You can also request task status.
     *
     * @see https://www.unisender.com/en/support/api/partners/exportcontacts/
     *
     * @param array $params
     *
     * @return false|string
     */
    public function taskExportContacts(array $params)
    {
        return $this->callMethod('async/exportContacts', $params);
    }

    /**
     * Get a results report of the delivery of messages in the given campaign.
     * Depending on the number of recipients in the list, a report on it may be prepared for some time.
     * After the report is ready, it will be sent to the URL specified in the notify_url parameter of
     * the async/getCampaignDeliveryStats method.
     *
     * You can also request task status.
     *
     * @see https://www.unisender.com/en/support/statistics/getcampaigndeliverystats/
     *
     * @param array $params
     *
     * @return false|string
     */
    public function taskGetCampaignDeliveryStats(array $params)
    {
        return $this->callMethod('async/getCampaignDeliveryStats', $params);
    }

    /**
     * Get task status
     *
     * @param array $params
     *
     * @return false|string
     */
    public function getTaskResult(array $params)
    {
        return $this->callMethod('async/getTaskResult', $params);
    }

    /**
     * The getCurrencyRates method allows you to get a list of all currencies in the UniSender system.
     *
     * @see https://www.unisender.com/en/support/api/common/getcurrencyrates/
     *
     * @return false|string
     */
    public function getCurrencyRates()
    {
        return $this->callMethod('getCurrencyRates');
    }

    /**
     * The method sends a message to the email address with a link to confirm the address as the return address.
     * After clicking on this link, you can send messages on behalf of this email address.
     *
     * @param array $params
     *
     * @see https://www.unisender.com/en/support/api/partners/validatesender/
     *
     * @return false|string
     */
    public function validateSender(array $params)
    {
        return $this->callMethod('validateSender', $params);
    }

    /**
     * The system will register the domain in the list for authentication and generate a dkim key for it.
     * Confirm the address on the domain to add the domain to the list.
     *
     * @see https://www.unisender.com/en/support/api/partners/setsenderdomain/
     *
     * @param array $params
     *
     * @return false|string
     */
    public function setSenderDomain(array $params)
    {
        return $this->callMethod('setSenderDomain', $params);
    }

    /**
     * Get domains list registrated by setSenderDomain api method.
     *
     * @see https://www.unisender.com/en/support/api/partners/setsenderdomain/
     *
     * @param array $params
     *
     * @return false|string
     */
    public function getSenderDomainList(array $params)
    {
        return $this->callMethod('getSenderDomainList', $params);
    }

    /**
     * The method returns an object with confirmed and unconfirmed sender’s addresses. Unconfirmed sender’s address
     * is the address to which the message was sent with a link to confirm the return address,
     * but the confirmation link wasn’t clicked.
     * To verify the return address, you can use the validateSender method.
     *
     * @see https://www.unisender.com/en/support/api/partners/getcheckedemail/
     *
     * @param array $params
     *
     * @return false|string
     */
    public function getCheckedEmail(array $params)
    {
        return $this->callMethod('getCheckedEmail', $params);
    }

    /**
     * This method return information about contact.
     *
     * @param array $params Array: email, api_key
     *
     * @return false|string
     */
    public function getContact(array $params)
    {
        return $this->callMethod('getContact', $params);
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

        if (preg_match('/([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])(\.' .
            '([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])){3}/', $result, $match)) {
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
     * @param       $methodName
     * @param array $params
     *
     * @return false|string
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
    public function getApiHost()
    {
        return $this->apiHost;
    }

    /**
     * @param string $apiHost
     */
    public function setApiHost($apiHost)
    {
        $this->apiHost = $apiHost;
    }
}
