# cakephp-async-orm

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
