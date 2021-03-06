<p align="center">
<a href="https://packagist.org/packages/znframework/package-authentication" rel="nofollow">
	<img src="https://img.shields.io/packagist/dt/znframework/package-authentication?style=flat-square" style="max-width:100%;"></a>
<a href="//packagist.org/packages/znframework/package-authentication" rel="nofollow">
	<img src="https://img.shields.io/github/v/release/znframework/package-authentication?style=flat-square&color=00BFFF" style="max-width:100%;"></a>
<a href="//packagist.org/packages/znframework/package-authentication" rel="nofollow">
	<img src="https://img.shields.io/github/release-date/znframework/package-authentication?style=flat-square" style="max-width:100%;"></a>
<a href="//packagist.org/packages/znframework/package-authentication" rel="nofollow">
	<img src="https://img.shields.io/github/license/znframework/package-authentication?style=flat-square" style="max-width:100%;"></a>
</p>

<h2>ZN Framework Authentication Package</h2>
<p>
Follow the steps below for installation and use.
</p>

<h3>Installation</h3>
<p>
You only need to run the following code for the installation.
</p>

```
composer require znframework/package-authentication
```

<h3>Documentation</h3>
<p>
Click for <a href="https://docs.znframework.com/kullanici-islemleri/tekil-kullanici-kutuphanesi">documentation</a> of your library.
</p>

<h3>Example Usage</h3>
<p>
Basic level usage is shown below.
</p>

```php
<?php require 'vendor/autoload.php';

ZN\ZN::run();

use ZN\Request\Post;

# Register
User::register
(
    [
        'username' => Post::username(),
        'password' => Post::password(),
        'usermail' => Post::email(),
        'address'  => Post::address(),
        'phone'    => Post::mobilePhone()  
    ],  
    'users/login'
);

Output::display(User::error());
```
```php
use ZN\Request\Post;

$status = User::login(Post::username(), Post::password(), Post::rememberMe());

if( $status === false )
{
    redirect(URL::prev());
}
```