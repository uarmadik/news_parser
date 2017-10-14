<?php

class Parser
{

    protected $url = 'http://www.pravda.com.ua/rus/news/';
    protected $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36';

    protected $host = '';

    public function init()
    {
        if ($_GET['quantity_news'] > 0){
            return $_GET['quantity_news'];
        } else {
            return false;
        }
    }

    public function get_web_page()
    {
        $url = $this->url;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        $content = curl_exec($ch);
        $err     = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $header  = curl_getinfo($ch);

        curl_close($ch);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;

        $this->host = parse_url($header['url'], PHP_URL_SCHEME). '://' . parse_url($header['url'], PHP_URL_HOST);

        if (($header['errno'] != 0) || ($header['http_code'] != 200)) {
            exit($header['errmsg']);

        } else {
            return $header['content'];
        }
    }

    public function get_page_elements($page, $elements_quantity=null)
    {
        $doc = phpQuery::newDocument($page);


        $all_articles =$doc->find('div.news_all .article');
        $count_articles = (!$elements_quantity) ? count($all_articles) : $elements_quantity;

        $all_content = array();
        for ($i=0; $i<$count_articles; $i++) {

            $article = pq($all_articles->elements[$i]);

            $content['news_number']    = $i;
            $content['date_time']      = date('d-m-Y H:i:s');
            $content['news_time']      = $article->find('.article__time')->text();
            $header_link               = $article->find('.article__title a')->attr('href');

            $content['header_href'] = (!parse_url($header_link, PHP_URL_HOST)) ? $this->host . $header_link : $header_link;

            $content['news_header']    = $article->find('.article__title a')->text();
            $content['news_subtitle']  = $article->find('.article__subtitle')->text();

            $all_content[$i] = $content;
        }
        return $all_content;
    }

    public function save_to_csv($all_content)
    {
        $table_header = ['Порядковий номер',
                         'Дата та час парсингу',
                         'Час новини',
                         'Посилання на новину',
                         'Заголовок новини',
                         'Короткий опис новини' ];

        array_unshift($all_content, $table_header);

        $fp = fopen('test.csv', 'w');

        foreach ($all_content as $row) {
            fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($fp, $row, ';');
        }

        fclose($fp);
    }

    public function show_table($all_content)
    {
        echo '<table cellspacing="0" border="1">';
        echo '<tr><th>Порядковий номер</th>
                  <th>Час новини</th>
                  <th>Дата та час парсингу</th>
                  <th>Посилання на новину</th>
                  <th>Короткий текст новини</th></tr>';
        foreach ($all_content as $content) {
            echo'<tr>'.
                  '<td>'. $content['news_number'] . '</td>' .
                  '<td>'. $content['date_time'] .   '</td>' .
                  '<td>'. $content['news_time'] .   '</td>' .
                  '<td><a href="' . $content['header_href'] . '" target="blank">'. $content['news_header'] . '</a></td>' .
                  '<td>'. $content['news_subtitle'].'</td>'.
                '</tr>';
        }
        echo '</table>';

        echo '<a href="/test.csv" class="btn">Завантажити таблицю у csv файлі</a>';
    }
}