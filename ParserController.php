<?php

namespace app\karma;

/*
 * Класс, при помощи которого можно распарсить отдельные части страницы
 */
class ParserController
{
    /*
     * Заголовок страницы
     */
    public $title;

    /*
     * Картинка со страницы
     */
    public $image;

    private function parser($url)
    {
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        /*
         * Если URL содержит https
         */
        if (preg_match('/https/',$url)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        $r=curl_exec($ch);
        curl_close($ch);

        /*
         * Определяем title
         */
        preg_match_all('/<title>(.*)<\/title>/siU', $r, $output_array);
        if (isset($output_array[1][0])) {
            $this->title = $output_array[1][0];
        }

        /*
         * Определяем image
         * Если присутствует тег og:image
         */
        preg_match_all('/property="og:image" content="(.*)"/siU', $r, $output_array);
        if (isset($output_array[1][0])) {
            $this->image = $output_array[1][0];
        } else {

            /*
             * Иначе, парсим первую картинку на странице
             */
            preg_match_all('/<img\ssrc="(.*)"/siU', $r, $output_array);
            if (isset($output_array[1][0])) {
                $this->image = $output_array[1][0];

                /*
                 * Проверим, есть ли в img домен
                 */
                $domain = parse_url($url, PHP_URL_HOST);
                $protocol = parse_url($url, PHP_URL_SCHEME);

                /*
                 * Если домена в img нету, то дописываем его
                 */
                if (!preg_match('/'.$domain.'/',$this->image)) {
                    $this->image = preg_replace('/^\//siU', '', $this->image);
                    $this->image = $protocol.'://'.$domain.'/'.$this->image;
                }
            }
        }

        /*
         * Если кодировка windows-1251, то преобразуем ее в utf-8
         */
        if (isset($r) && preg_match_all('/charset=windows-1251/', $r)) {
            $this->title = mb_convert_encoding($this->title, 'utf-8', 'cp-1251');
        }

    }

    public function run($url)
    {
        /*
         * Парсинг картинки и заголовка со страницы
         * Коды ответа проверки(code):
         * 1 => Данные успешно получены
         * 2 => Заголовок и картинка со страницы не получены
         * 3 => Не задана ссылка
         */
        if (isset($url)) {

            /*
             * Парсим данные со страницы
             */
            $this->parser($url);
            if ($this->title && $this->image) {
                $array = array(
                    'status' => 'success',
                    'code' => '1',
                    'message' => 'Данные успешно получены'
                );
                return json_encode($array);
            } else {
                $array = array(
                    'status' => 'error',
                    'code' => '2',
                    'message' => 'Заголовок и картинка со страницы не получены'
                );
                return json_encode($array);
            }

        } else {
            $array = array(
                'status' => 'error',
                'code' => '3',
                'message' => 'Не задана ссылка'
            );
            return json_encode($array);
        }
    }

    /*
     * Возвращает заголовок со страницы
     */
    public function getTitle()
    {
        return $this->title;
    }

    /*
     * Возвращает изображение со страницы
     */
    public function getImage()
    {
        return $this->image;
    }

}