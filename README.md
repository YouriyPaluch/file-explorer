# file-explorer
Бібліотека, яка дозволяє створити в проєкті сторінку для перегляду файлів і тек по аналогії з файловим менеджером операційної системи. Може відображати вміст будь-якого шляху проєкту.


### Приклад використання

```
echo (new YouriyPaluch\FileExplorer\Dispatcher(
PROJECT_ROOT . '/logs',
'show-logs',
['txt', 'log'],
['.gitignore', '.keep', '.gitkeep'],
))->showContent();
```
