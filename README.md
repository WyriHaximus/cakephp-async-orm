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
