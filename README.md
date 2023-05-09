# WP_comment_GPT
Плагин для WordPress - комментатор на базе ChatGPT

##Description
Искусственный интеллект (ChatGPT) оставляет осмысленный комментарий к записям WordPress. Дополняет новость уникальным контентом! Стимулирует дальнейшую дискуссию читателями! 

##Plugin Options and Settings
Для полноценной работы требуется: API-key: https://platform.openai.com/account/api-keys
Тонкие настройки плагина в консоли WordPress: НАСТРОЙКИ - ОБСУЖДЕНИЯ:
- Укажите Email от имени которого будут публиковаться комментарии;
- Укажите "Имя комментатора" или несколько (разделенных запятыми) из которых будет выбран случайный логин;
- Укажите "Температуру" (от 0 до 1): чем больше, тем уникальнее комментарий от предыдущего, но и время генерации больше;
- Поставьте или снимите галочку "Генерация комментария при СОЗДАНИИ/РЕДАКТИРОВАНИИ записи";

Комментарий к записям WordPress генерируется:
- при создания новой или при редактировании существующей записи (кнопка СОЗДАТЬ/СОХРАНИТЬ в редакторе записи) - включается опционально;
- В списке записей (Записи - Все записи) выбрать нужные, в выпадающем списке "Действия" выбрать пункт "GPT комментарий";


