<?php
namespace Model\Registry;

class SmsC {
    private $url = 'https://smsc.ru';
    private $login = '';
    private $password = '';
    private $sign = '';
    private $status = false;

    public function __construct($login, $password, $sign = false){
        $this->log_sms = new \Log('sms.log');

        $this->login = $login;
        $this->password = $password;

        if ($sign)
            $this->sign = $sign;
    }

    /**
     * Формирование curl запроса
     * @param $url
     * @param $post
     * @param $options
     * @return mixed
     */
    private function curl_post($url, array $post = NULL, array $options = array()){
        $post['login'] = $this->login;
        $post['psw'] = $this->password;
        $post['fmt'] = 3;
        $post['charset'] = 'utf-8';

        if ($this->sign)
            $post['sender'] = $this->sign;

        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => http_build_query($post),
            // CURLOPT_SSL_VERIFYPEER => 0,
            // CURLOPT_SSL_VERIFYHOST => 0
        );

        $ch = curl_init();

        curl_setopt_array($ch, ($options + $defaults));

        if (!$result = curl_exec($ch)) {
            $this->status = 0;

            return array('error' => curl_error($ch));
        }

        curl_close($ch);

        $result = json_decode($result, true);

        $this->status = empty($return['error_code']) && empty($return['error']);

        return $result;
    }

    /**
     * Отправка сообщения
     * @param $to string|array  - Номер телефона(ов)
     * @param $msg string       - Текст сообщения
     * @param $time integer     - Дата для отложенной отправки сообщения (в формате unixtime)
     */
    public function send($number, $text, $channel = '', $dateSend = null, $callbackUrl = null){
        $return = $this->curl_post($this->url . '/sys/send.php', [
            'phones' => is_array($number) ? implode(',', $number) : $number,
            'mes' => $text,
            // 'time' => $dateSend
        ]);

        $data = array();

        if (isset($return['id']))
            $data[] = array(
                "id" => (int)preg_replace('/\D/', '', $return['id']),
                "number" => $number,
                "status" => empty($return['error'])
            );

        return $data;
    }

    /**
     * Проверка статуса SMS сообщения
     * @param sms_id - Идентификатор сообщения
     * @return array
     */
    public function check_send($id){
        return $this->curl_post($this->url . '/sys/status.php', [
            'id' => $id
        ]);
    }

    /**
     * Получение списка отправленных sms сообщений
     * @param $number string - Фильтровать сообщения по номеру телефона
     * @param $text string   - Фильтровать сообщения по тексту
     * @param $page integer  - Номер страницы
     * @return array
     */
    public function sms_list($number = null, $text = null, $page = null){
        return $this->curl_post($this->url . '/sms/list' . (isset($page) ? '?page=' . $page : ''), [
            'number' => $number,
            'text' => $text
        ]);
    }

    /**
     * Запрос баланса
     * @return array
     */
    public function balance(){
        $return = $this->curl_post($this->url . '/sys/balance.php', []);

        if (!isset($return['balance']))
            $return['balance'] = 0;

        return $return;
    }

    /**
     * Запрос баланса
     * @param $min integer - Минимальная сумма на счету
     * @return bool
     */
    public function has_balance($min = 0){
        $return = $this->balance();

        return $return['balance'] > $min;
    }

    /**
     * Запрос тарифа
     * @return array
     */
    public function tariffs(){
        return $this->curl_post($this->url . '/tariffs', []);
    }

    /**
     * Добавление подписи
     * @param $name - Имя подписи
     * @return array
     */
    public function sign_add($name){
        return $this->curl_post($this->url . '/sign/add', [
            'name' => $name
        ]);
    }

    /**
     * Получить список подписей
     * @return array
     */
    public function sign_list(){
        return $this->curl_post($this->url . '/sign/list' . (isset($page) ? '?page=' . $page : ''), []);
    }

    /**
     * Добавление группы
     * @param $name string - Имя  группы
     * @return array
     */
    public function group_add($name){
        return $this->curl_post($this->url . '/group/add', [
            'name' => $name
        ]);
    }

    /**
     * Удаление группы
     * @param $id integer - Идентификатор группы
     * @return array
     */
    public function group_delete($id){
        return $this->curl_post($this->url . '/group/delete', [
            'id' => $id
        ]);
    }

    /**
     * Получение списка групп
     * @param $page integer - Пагинация
     * @return array
     */
    public function group_list($page = null){
        return $this->curl_post($this->url . '/group/list' . (isset($page) ? '?page=' . $page : ''), []);
    }

    /**
     * Добавление контакта
     * @param $number string - Номер абонента
     * @param null $groupId integer - Идентификатор группы
     * @param null $birthday integer - Дата рождения абонента (в формате unixtime)
     * @param null $sex string - Пол
     * @param null $lname string - Фамилия абонента
     * @param null $fname string - Имя абонента
     * @param null $sname string - Отчество абонента
     * @param null $param1 string - Свободный параметр
     * @param null $param2 string - Свободный параметр
     * @param null $param3 string - Свободный параметр
     * @return array
     */
    public function contact_add($number, $groupId = null, $birthday = null, $sex = null, $lname = null,
            $fname = null, $sname = null, $param1 = null, $param2 = null, $param3 = null){
        return $this->curl_post($this->url . '/contact/add', [
            'number' => $number,
            'groupId' => $groupId,
            'birthday' => $birthday,
            'sex' => $sex,
            'lname' => $lname,
            'fname' => $fname,
            'sname' => $sname,
            'param1' => $param1,
            'param2' => $param2,
            'param3' => $param3
        ]);
    }

    /**
     * Удаление контакта
     * @param $id integer - Идентификатор абонента
     * @return array
     */
    public function contact_delete($id){
        return $this->curl_post($this->url . '/contact/delete', [
            'id' => $id
        ]);
    }
    /**
     * Список контактов
     * @param null $number string - Номер абонента
     * @param null $groupId integer - Идентификатор группы
     * @param null $birthday integer - Дата рождения абонента (в формате unixtime)
     * @param null $sex string - Пол
     * @param null $operator string - Оператор
     * @param null $lname string - Фамилия абонента
     * @param null $fname string - Имя абонента
     * @param null $sname string - Отчество абонента
     * @param null $param1 string - Свободный параметр
     * @param null $param2 string - Свободный параметр
     * @param null $param3 string - Свободный параметр
     * @param null $page integer - Пагинация
     * @return array
     */
    public function contact_list($number = null, $groupId = null, $birthday = null, $sex = null, $operator = null,
            $lname = null, $fname = null, $sname = null, $param1 = null, $param2 = null, $param3 = null, $page = null){
        return $this->curl_post($this->url . '/contact/list' . (isset($page) ? '?page=' . $page : ''), [
            'number' => $number,
            'groupId' => $groupId,
            'birthday' => $birthday,
            'sex' => $sex,
            'operator' => $operator,
            'lname' => $lname,
            'fname' => $fname,
            'sname' => $sname,
            'param1' => $param1,
            'param2' => $param2,
            'param3' => $param3
        ]);
    }

    /**
     * Добавление в чёрный список
     * @param $number array|string - Номера телефонов|Номер абонента
     * @return array
     */
    public function blacklist_add($number){
        return $this->curl_post($this->url . '/blacklist/add', [
            is_array($number) ? 'numbers' : 'number' => $number
        ]);
    }

    /**
     * Удаление из черного списка
     * @param $id integer - Идентификатор абонента
     * @return array
     */
    public  function blacklist_delete($id){
        return $this->curl_post($this->url. '/blacklist/delete', [
            'id' => $id
        ]);
    }

    /**
     * Список контактов в черном списке
     * @param null $number string - Номер абонента
     * @param null $page integer - Пагинация
     * @return array
     */
    public function blacklist_list($number = null, $page = null){
        return $this->curl_post($this->url . '/blacklist/list' . (isset($page) ? '?page=' . $page : ''), [
            'number' => $number
        ]);
    }

    /**
     * Создание запроса на проверку HLR
     * @param $number array|string - Номера телефонов|Номер абонента
     * @return array
     */
    public function hlr_check($number){
        return $this->curl_post($this->url . '/hlr/check', [
            is_array($number) ? 'numbers' : 'number' => $number
        ]);
    }

    /**
     * Получение статуса HLR
     * @param $id integer - Идентификатор запроса
     * @return array
     */
    public function hlr_status($id){
        return $this->curl_post($this->url. '/hlr/status', [
            'id' => $id
        ]);
    }

    /**
     * Определение оператора
     * @param $number array|string - Номера телефонов|Номер абонента
     * @return array
     */
    public function number_operator($number){
        return $this->curl_post($this->url . '/number/operator', [
            is_array($number) ? 'numbers' : 'number' => $number
        ]);
    }

    /**
     * Отправка Viber-рассылок
     * @param null $number string|array - Номер телефона|Номера телефонов. Максимальное количество 50
     * @param null $groupId integer|string - ID группы по которой будет произведена рассылка.
        Для выбора всех контактов необходимо передать значение "all"
     * @param $sign string - Подпись отправителя
     * @param $channel string - Канал отправки Viber
     * @param $text string - Текст сообщения
     * @param null $imageSource string - Картинка кодированная в base64 формат, не должна превышать размер 300 kb.
     * Отправка поддерживается только в 3 форматах: png, jpg, gif. Перед кодированной картинкой
        необходимо указывать её формат.
     * Пример: jpg#TWFuIGlzIGRpc3Rpbmd1aXNoZ. Отправка доступна только методом POST.
        Параметр передается совместно с textButton и linkButton
     * @param null $textButton string - Текст кнопки. Максимальная длина 30 символов.
        Параметр передается совместно с imageSource и linkButton
     * @param null $linkButton string - Ссылки для перехода при нажатие кнопки.
        Ссылка должна быть с указанием http:// или https://.
        Параметр передается совместно с imageSource и textButton
     * @param null $dateSend integer - Дата для отложенной отправки рассылки (в формате unixtime)
     * @param null $signSms string - Подпись для SMS-рассылки. Используется при выборе канала "Viber-каскад"
        (channel=CASCADE). Параметр обязателен
     * @param null $channelSms string - Канал отправки SMS-рассылки. Используется при выборе канала "Viber-каскад"
        (channel=CASCADE). Параметр обязателен
     * @param null $textSms string - Текст сообщения для SMS-рассылки. Используется при выборе канала "Viber-каскад"
        (channel=CASCADE). Параметр обязателен
     * @param null $priceSms integer - Максимальная стоимость SMS-рассылки. Используется при выборе канала "Viber-каскад"
        (channel=CASCADE). Если параметр не передан, максимальная стоимость будет рассчитана автоматически
     * @return array
     */
    public function viber_send($number = null, $groupId = null, $sign, $channel, $text, $imageSource = null, $textButton = null,
            $linkButton = null, $dateSend = null, $signSms = null, $channelSms = null, $textSms = null, $priceSms = null){
        return $this->curl_post($this->url . '/viber/send', [
            is_array($number) && !empty($number) ? 'numbers' : 'number' => $number,
            'groupId' => $groupId,
            'sign' => $sign,
            'channel' => $channel,
            'text' => $text,
            '$imageSource' => $imageSource,
            'textButton' => $textButton,
            'linkButton' => $linkButton,
            'dateSend' => $dateSend,
            'signSms' => $signSms,
            'channelSms' => $channelSms,
            'textSms' => $textSms,
            'priceSms' => $priceSms
        ]);
    }

    /**
     * Статистика по Viber-рассылке
     * @param $sendingId integer - Идентификатор Viber-рассылки в системе
     * @param $page integer - Пагинация
     * @return array
     */
    public function viber_statistic($sendingId, $page = null){
        return $this->curl_post($this->url . '/viber/statistic' . (isset($page) ? '?page=' . $page : ''), [
            'sendingId' => $sendingId
        ]);
    }

    /**
     * Список Viber-рассылок
     * @return array
     */
    public function viber_list(){
        return $this->curl_post($this->url . '/viber/list', []);
    }

    /**
     * Список доступных подписей для Viber-рассылок
     * @return array
     */
    public function viber_sign_list(){
        return $this->curl_post($this->url . '/viber/sign/list', []);
    }

    /**
     * Проверка статуса
     * @return bool
     */
    public function status_ok(){
        return $this->status;
    }
}