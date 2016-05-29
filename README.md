# cakephp-async-orm

[![Linux Build Status](https://travis-ci.org/WyriHaximus/cakephp-async-orm.png)](https://travis-ci.org/WyriHaximus/cakephp-async-orm)
[![Latest Stable Version](https://poser.pugx.org/WyriHaximus/cake-async-orm/v/stable.png)](https://packagist.org/packages/WyriHaximus/cake-async-orm)
[![Total Downloads](https://poser.pugx.org/wyrihaximus/cake-async-orm/downloads.png)](https://packagist.org/packages/wyrihaximus/cake-async-orm)
[![Code Coverage](https://scrutinizer-ci.com/g/WyriHaximus/cakephp-async-orm/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WyriHaximus/cakephp-async-orm/?branch=master)
[![License](https://poser.pugx.org/wyrihaximus/cake-async-orm/license.png)](https://packagist.org/packages/wyrihaximus/cake-async-orm)
[![PHP 7 ready](http://php7ready.timesplinter.ch/WyriHaximus/cakephp-async-orm/badge.svg)](https://travis-ci.org/WyriHaximus/cakephp-async-orm)

Asynchronous access to cake3 models in async projecs, currently in early Alpha stage.

# Example

```php
<?php

namespace App\Shell;

use Cake\Console\Shell;
use React\EventLoop\Factory;
use WyriHaximus\React\Cake\Orm\AsyncTableRegistry;

class ScreenshotsShell extends Shell
{
    public function status()
    {
        $loop = Factory::create();
        AsyncTableRegistry::init($loop);

        // Keep in mind that ALL methods on the AsyncTable you get from the AsyncTableRegistry is a promise
        AsyncTableRegistry::get('Screenshots')->find('all')->then(function ($data) use ($loop) {
            var_export($data);
            $loop->stop();
        }, function ($error) use ($loop) {
            var_export($error);
            $loop->stop();
        });

        $loop->run();
    }
}
```

# (A)sync detection

In order to only run the necessary calls to the table object on the pool several detection strategies have been put in place, namely:

* Docblock return type, if it matches `Cake\ORM\Query` it will ignore any annotations or function names
* Annotations Async and Sync can be used class wide but overwritten on the method level
* Function name detection, `fetch*`, `find*`, and `retrieve*` will be async and the rest sync unless overwritten by annotations or return type

## License ##

Copyright 2015 [Cees-Jan Kiewiet](http://wyrihaximus.net/)

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
