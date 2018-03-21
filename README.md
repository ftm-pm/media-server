# MediaServer

The MediaServer is a micro application that receives an image file with filters and returns 
the path to this image and a collection of images with applied filters.

Russian documentation [here][doc].

## Setup

The MediaServer is a [symfony/skeleton][1] application with packages for uploading and cropping images. 
Some bundles:

 * [VichUploaderBundle][2] - uploading images
 * [LiipImagineBundle][3] - creating preview images
 * [LexikJWTAuthenticationBundle][4] -  authorization JWT
 * [All bundles](#bundles) 

### Installation

Run the Composer command to create a new project
```bash
composer create-project ftm-pm/media-server my-project
```

### Configuration

After installing, you need to set environment variables. You can see variables in the .env file. 

Next step, run command to update database.
```bash
php bin/console d:s:u --force
```

In MediaServer, authorization was developed using JWT. You can see documentation [here][jwt].

For create a new user, you can use any REST client. You should send a new request to 
http://my-project/api/register with parameters:
```json
{
  "username": "johndoe",
  "password": "test",
  "email": "johndoe@example.com"
}
```

or using curl
```bash
curl -X POST http://my-project/api/register -d username=johndoe -d password=test -d email=johndoe@example.com
```
After the confirmation email, get token. Send a new request to http://my-project/api/token/get:
```json
{
  "username": "johndoe",
  "password": "test"
}
```

or using curl
```bash
curl -X POST http://my-project/api/token/get -d username=johndoe -d password=test
```

The MediaServer API returns two fields: 
```json
{
  "token": "...",
  "refresh_token": "..."
}
```

For authorization, you must to send header for any request: Authorization: Bearer your_token.

## Use

The MediaServer API can create(load, crop...) and remove images. 
``curl -X POST  http://my-project/api/images  ... `` - to create
``curl -X DELETE  http://my-project/api/images/id  ... `` - to delete by id

### Simple loading 

For create a new image, you can send a new **post**  request with parameter ``imageFile``:
```bash
curl -X POST -H "Authorization: Bearer your_token" -H "Content-Type: multipart/form-data" -F "imageFile=@/path/for/your/file.jpg" http://my-project/api/images
```

The MediaServer API returns one field with the path to the source image:
```json
{
  "origin": "http://my-project/uploads/images/5ab/1e4/f82/5ab1e4f821d62240251619.jpg"
}
```

### Создание превью

You can create a different preview for image using [LiipImagineBundle][3]. In the api, the ``previews`` parameter is an array consisting of LiipImagineBundle 
filter configurations.

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

For example, creating a ``small`` preview:
```bash
curl -X POST -H "Authorization: Bearer your_token" -H "Content-Type: multipart/form-data" -F "imageFile=@/path/for/your/file.jpg"  -F "previews[small][thumbnail][size][0]=100" -F "previews[small][thumbnail][size][]=50" http://my-project/api/images
```

The MediaServer API returns the path to the source image, as well as a previews collection:
```json
{
  "origin": "http://my-project/uploads/images/5ab/1ed/5b5/5ab1ed5b538e9914783874.jpg",
  "previews": {
    "small": "http://my-project/media/cache/view1/rc/qcJ6p4ur/uploads/images/5ab/1ed/5b5/5ab1ed5b538e9914783874.jpg"
  }
}
```

<a name="bundles"><h2>What's inside</h2></a>

It's the symfony 4 skeleton with the following bundles:

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
 
## Feedback
 
* Create a new issue
* Ask a question on [сайте](https://ftm.pm).
* Send a message to fafnur@yandex.ru

License [MIT][license].

[1]: https://github.com/symfony/skeleton
[2]: https://github.com/dustin10/VichUploaderBundle
[3]: https://github.com/liip/LiipImagineBundle
[4]: https://github.com/lexik/LexikJWTAuthenticationBundle
[composer]: https://getcomposer.org/
[doc]: https://github.com/ftm-pm/media-server/blob/master/docs/ru/readme.md
[jwt]: https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md
[license]: https://github.com/ftm-pm/media-server/blob/master/LICENSE.txt
