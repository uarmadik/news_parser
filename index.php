<?php
//phpQuery
require_once 'phpQuery/phpQuery-onefile.php';

// Class Parser
require_once 'Parser.php';

$parser = new Parser();
if ($parser->init()) {
    $page = $parser->get_web_page();
    $all_content = $parser->get_page_elements($page, $parser->init());
    $parser->save_to_csv($all_content);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parser</title>
    <style>
        body {
            padding: 0.5em;
        }
        .btn {
            display: block;
            width: fit-content;
            margin: .5em 0;
            padding: .5em 1em;
            border: 1px solid #444;
            border-radius: .5em;
            background-color: #999;
            color: #fff;
            font: bold 1em sans-serif;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #555;
        }


            form input {
                display: block;
                margin: .5em;
                padding: .5em;
                width: 150px;
            }
                label input {
                    display: inline-block;
                    width: 50px;
                    padding: .5em;
                }
    </style>
</head>
<body>
    <form action="/">
        <label>
            Максимальна кількість новин для парсингу
            <input type="number" name="quantity_news" step="1" min="0" value="5">
        </label>
        <input type="submit" value="Парсити" class="btn">
    </form>
    <a href="/" class="btn">Очистити</a>

    <?php
        // shows content in table if it is
        if ($all_content) {
            $parser->show_table($all_content);
        }
    ?>


</body>
</html>
