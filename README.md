# ParserController

[![N|Solid](https://travelinlife.ru/uploads/files/4_Grayscale_logo_on_transparent_238x69pngpng.png)](https://travelinlife.ru/)


ParserController - Класс, при помощи которого можно распарсить отдельные части страницы:

  - Title
  - Image

### Использование

```sh
$parser = new ParserController();
```

### Получение статуса операции

```sh
$status = $parser->run('https://travelinlife.ru');
var_dump($status);
```

### Вывести заголовок

```sh
echo $parser->getTitle();
```

### Вывести ссылку на картинку

```sh
echo $parser->getImage();
```