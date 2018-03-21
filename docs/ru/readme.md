# MediaServer

MediaServer это микро приложение, которое получает изображение с набором фильтров и возвращает путь 
до оригинального изображения и коллекцию изображений с примененными фильтрами.

Документация (EN) [здесь][doc].

## Настройка

MediaServer представляет собой [symfony/skeleton][1] сборку с бандлами для загрузки и обработки 
изображений.
 * [VichUploaderBundle][2] - загрузка и сохранение изображений
 * [LiipImagineBundle][3] - создание превью изображений
 * [LexikJWTAuthenticationBundle][4] - JWT авторизация
 * [Полный список бандлов](#bundles) 

### Установка

Создание приложения с помощью [Composer][composer]
```bash
composer create-project ftm-pm/media-server my-project
```

### Конфигурация

Указываем переменные окружения для приложения.

Запускаем команду для создания базы. 
```bash
php bin/console d:s:u --force
```

В приложении по умолчанию реализована авторизация пользователя с помощью JWT. Документация по бандлу реализующему 
 JWT [здесь][jwt]. 
Для создания пользователя можете использовать любой REST client, отправив post запрос 
на http://my-project/api/register с данными:
```json
{
  "username": "johndoe",
  "password": "test",
  "email": "johndoe@example.com"
}
```

или используя curl
```bash
curl -X POST http://my-project/api/register -d username=johndoe -d password=test -d email=johndoe@example.com
```
После подтверждения email, получаем токен отправляя запрос http://my-project/api/token/get:
```json
{
  "username": "johndoe",
  "password": "test"
}
```

или используя curl
```bash
curl -X POST http://my-project/api/token/get -d username=johndoe -d password=test
```

MediaServer API вернет 2 текстовых поля: 
```json
{
  "token": "...",
  "refresh_token": "..."
}
```

## Использование

Для авторизации в приложении отправляем заголовок: Authorization: Bearer your_token.

MediaServer API  может создавать и удалять изображения. 
``curl -X POST  http://my-project/api/images  ... `` - создание изображения
``curl -X DELETE  http://my-project/api/images/<id>  ... `` - удаления изображения по <id>

### Обычная загрузка

Для создания изображения используется **post** запрос на адрес  
c 1 обязательным параметром imageFile:
```bash
curl -X POST -H "Authorization: Bearer your_token" -H "Content-Type: multipart/form-data" -F "imageFile=@/path/for/your/file.jpg" http://my-project/api/images
```

MediaServer API вернет путь до сохраненного изображения:
```json
{
  "origin": "http://my-project/uploads/images/5ab/1e4/f82/5ab1e4f821d62240251619.jpg"
}
```

### Создание превью

Для создания различных превью изображения используется [LiipImagineBundle][3]. Параметр ``previews`` 
представляет объект, свойствами которого являются наборы фильтров LiipImagineBundle. 

```json
{
  "previews": {
    "small": {
      "thumbnail": {
        "size": [50, 50]
      }
    },
    "large": {
      "thumbnail": {
        "size": [50, 50]
      },
      "background": { 
        "size": [124, 94], 
        "position": "center", 
        "color": "#000000"
      }
    }
  }
}
```

Например, создадим мини превью ``small``:
```bash
curl -X POST -H "Authorization: Bearer your_token" -H "Content-Type: multipart/form-data" -F "imageFile=@/path/for/your/file.jpg"  -F "previews[small][thumbnail][size][0]=100" -F "previews[small][thumbnail][size][]=50" http://my-project/api/images
```

MediaServer API вернет путь до сохраненного изображения, а также коллекцию previews:
```json
{
  "origin": "http://my-project/uploads/images/5ab/1ed/5b5/5ab1ed5b538e9914783874.jpg",
  "previews": {
    "small": "http://my-project/media/cache/view1/rc/qcJ6p4ur/uploads/images/5ab/1ed/5b5/5ab1ed5b538e9914783874.jpg"
  }
}
```

Стоит отметить, что по-умолчанию создано ограничение в 5 превью за 1 запрос.
В дальнейшем это будет вынесено в отдельную переменную окружения. 

Также большинство частей генерируемого пути настраивается в бандлах обработки изображений, так что можно достигнуть результата вида
```json
{
  "origin": "http://my-project/image/5ab1ed5b538e9914783874.jpg",
    "previews": {
      "small": "http://my-project/v1/rc/5ab1ed5b538e9914783874.jpg"
    }
}
```

## FAQ
 - **Использование без JWT**
 
    Удаляем банды: lexik/jwt-authentication-bundle, gesdinet/jwt-refresh-token-bundle, 
    gfreeau/get-jwt-bundle.
    
    Затем удаляем все связанные с пользователем файлы: User, UserController, UserHandler, UserRepository, UserExistsException, UserNotFoundException.
    
    В DoctrineEventSubscriber удаляем логику связанную с User.
 
 - **Зачем нужен guzzlehttp/guzzle?**
    
    LiipImagineBundle создает изображение только при обращении, поэтому приходиться делать доп. запрос при создании.
    Можно убрать данную логику, только тогда потеряются красивые URL к превью.

<a name="bundles"><h2>Что включено</h2></a>

 * ext-iconv
 * gesdinet/jwt-refresh-token-bundle
 * gfreeau/get-jwt-bundle
 * guzzlehttp/guzzle
 * lexik/jwt-authentication-bundle
 * liip/imagine-bundle
 * nelmio/cors-bundle
 * sensio/framework-extra-bundle
 * symfony/console
 * symfony/flex
 * symfony/form
 * symfony/framework-bundle,
 * symfony/lts
 * symfony/maker-bundle
 * symfony/orm-pack
 * symfony/security-bundle,
 * symfony/swiftmailer-bundle
 * symfony/twig-bundle
 * symfony/validator
 * symfony/yaml
 * vich/uploader-bundle
 
## Обратная связь
 
* Создать issue в проекте
* Задать вопрос на [сайте](https://ftm.pm).
* Написать на почту fafnur@yandex.ru

Лицензия [MIT][license].

[1]: https://github.com/symfony/skeleton
[2]: https://github.com/dustin10/VichUploaderBundle
[3]: https://github.com/liip/LiipImagineBundle
[4]: https://github.com/lexik/LexikJWTAuthenticationBundle
[composer]: https://getcomposer.org/
[doc]: https://github.com/ftm-pm/media-server/blob/master/README.md
[jwt]: https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md
[license]: https://github.com/ftm-pm/media-server/blob/master/LICENSE.txt
