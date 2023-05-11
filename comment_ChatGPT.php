<?php
/*
Plugin Name: WP Комментатор ChatGPT
Plugin URI: https://sochka.com
Description: Искусственный интеллект (ChatGPT) оставляет осмысленный комментарий к записям (каждый раз: при создании новой или редактировании старой, а также к избранным записям). Дополняет новость уникальным контентом! Стимулирует дальнейшую дискуссию читателями!
Version: 0.37
Requires at least: 5.2
Author: Yaroslav Sochka
Author URI: https://sochka.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

//настройки плагина
add_action( 'admin_init', 'gpt_settings_api_init' );

function gpt_settings_api_init() {
	add_settings_section(
		'gpt_setting_section',
		'Настройки комментатора GPT',
		'gpt_setting_section_callback_function',
		'discussion'
	);
	add_settings_field(
		'gpt_setting_name',
		'API ключ GPT',
		'gpt_setting_callback_function',
		'discussion',
		'gpt_setting_section'
	);
	add_settings_field(
		'gpt_setting_name2',
		'Email от имени которого будут публиковаться комментарии',
		'gpt_setting_callback_function2',
		'discussion',
		'gpt_setting_section'
	);
		add_settings_field(
		'gpt_setting_name3',
		'Имя комментатора',
		'gpt_setting_callback_function3',
		'discussion',
		'gpt_setting_section'
	);
		add_settings_field(
		'gpt_setting_name4',
		'Температура (от 0 до 1)',
		'gpt_setting_callback_function4',
		'discussion',
		'gpt_setting_section'
	);
		add_settings_field(
		'gpt_setting_name5',
		'',
		'gpt_setting_callback_function5',
		'discussion',
		'gpt_setting_section'
	);


	register_setting( 'discussion', 'gpt_setting_name' );
	register_setting( 'discussion', 'gpt_setting_name2' );
	register_setting( 'discussion', 'gpt_setting_name3' );
	register_setting( 'discussion', 'gpt_setting_name4' );
	register_setting( 'discussion', 'gpt_setting_name5' );
}


function gpt_setting_section_callback_function() {
    echo '<section id="gptservices">';
	echo '<p>Внимательно заполните ВСЕ поля для корректной работы комментатора ChatGPT</p>';
	echo '<p>Чтобы GPT комментировал: <b>Записи - Все записи</b>, выбрать нужные, в выпадающем списке "<b>Действия</b>" выбрать пункт "<b>GPT комментарий</b>"</p>';
	echo '</section>';
}

function gpt_setting_callback_function() {
	?>
	<input
		name="gpt_setting_name"
		type="text"
		value="<?= esc_attr( get_option(  'gpt_setting_name' ) ) ?>"
	/>  <a href="https://platform.openai.com/account/api-keys" target="_blank" title="Требуется регистрация">Получить API</a>
					<?php 
}

function gpt_setting_callback_function2() {
	?>
	<input
		name="gpt_setting_name2"
		type="text"
		value="<?= esc_attr( get_option( 'gpt_setting_name2' ) ) ?>"
	 /> (оптимально, если у него есть Gravatar)
	<?php
}

function gpt_setting_callback_function3() {
	?>
	<textarea rows="10" cols="45" style="height: 50px;"
		name="gpt_setting_name3">
<?= esc_attr( get_option( 'gpt_setting_name3' ) ) ?></textarea>
 Например: newsBOT (если написать список имен, разделенных запятыми, то будет использовано случайное имя из списка)
	<?php
}

function gpt_setting_callback_function4() {
	?>
<input name="gpt_setting_name4" type="range" min="0" max="1" step="0.01" value="<?= esc_attr( get_option( 'gpt_setting_name4' ) ) ?>" oninput="updateValue(this.value)">
<b><span id="gptvalue"><?= esc_attr( get_option( 'gpt_setting_name4' ) ) ?></span></b>
<script>function updateValue(value) {document.getElementById("gptvalue").innerHTML = value;}</script>
 Чем выше, тем оригинальнее будут комментарии, но скорость генерации выше
	<?php
}

function gpt_setting_callback_function5() {
?>
	  <input type="checkbox" name="gpt_setting_name5" <?php checked(1, get_option('gpt_setting_name5'), true); ?> value="1" />
Генерация комментария при СОЗДАНИИ/РЕДАКТИРОВАНИИ записи
<?php
}

//ссылка на настройки плагина
function gpt_plugin_links( $links, $file ) {
    if ( $file == plugin_basename( __FILE__ ) ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'options-discussion.php#gptservices' ) ) . '">Настройки</a>';
        array_push( $links, $settings_link );
    }
    return $links;
}
add_filter( 'plugin_action_links', 'gpt_plugin_links', 10, 2 );


//генерируем GPT комментарий к записи по id

function add_comment_to_post($post_id) {
  $prompt_gpt = array(
        "Поругай автора за статью одним предложением: ",
        "Похвали автора за статью одним предложением: ",
        "Придумай простейшее двустишие или трёхстишие к тексту: ",
        "Придумай скептический комментарий к тексту: ",
        "Придумай короткую шутку на тему: ",
        "Напиши краткий афоризм на тему: ",
        "Напиши восторженный комментарий к тексту: ",
        "Сочини философское высказывание к тексту: ",
        "Какая пословица бы подошла к этому тексту: ",
        "Прокомментируй текст от имени читателя: ",
        "Придумай комментарий к тексту от имени человека, который ничего в этом не понимает: ",
	    "Задай наводящий вопрос, чтобы вызвать обсуждение, исходя из текста: ",
	    "Попроси автора расширить конкретный момент или идею, обсуждаемую в сообщении: ",
	    "Предложи конкретный комплимент или конструктивную критику к тексту: ",
        "Придумай саркастический комментарий к тексту от имени человека, который считает что все знает: "
    );

$fraza = $prompt_gpt[array_rand($prompt_gpt)];	
$query = $fraza.' '.get_the_title( $post_id );
$api_key_gpt = esc_attr( get_option(  'gpt_setting_name' ) );
$temperaturegpt =  esc_attr( get_option(  'gpt_setting_name4' ) );
$imenos = esc_attr( get_option('gpt_setting_name3'));
$words = explode(",", $imenos);
$random_imeno = trim($words[array_rand($words)]);
	
  $ch = curl_init();
    $post_fields = array(
        "model" => "gpt-3.5-turbo",
        "messages" => array(
            array(
                "role" => "user",
                "content" => $query
            )
        ),
        "max_tokens" => 512,
		"top_p" => 1,
        "frequency_penalty" => 0,
        "presence_penalty" => 0,
        "stop" => ["\\n"],
        "temperature" => floatval($temperaturegpt)
    );

    $header  = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key_gpt
    ];

	header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');
	
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);
$commentbot = $response['choices'][0]['message']['content'];

$data = array(
    'comment_post_ID' => $post_id,
    'comment_author' => $random_imeno,
    'comment_author_email' => esc_attr( get_option(  'gpt_setting_name2' ) ),
    'comment_content' => wp_kses_post($commentbot),
	'comment_parent'       => 0,
	'comment_author_IP'    => '127.0.0.1',
	'comment_date' => date('Y-m-d H:i:s'),
	'comment_approved'     => 1
        );
	
if (strlen($commentbot) > 10) { //проверяем, не пустой ли комментарий
$comment_id = wp_insert_comment(wp_slash($data));
}
    if ($comment_id) {
        // Комментарий успешно добавлен
        return true;
    } else {
        // Произошла ошибка при добавлении комментария
        return false;
    }
 }

//генерируем комментарий в момент СОЗДАНИЯ/СОХРАНЕНИЯ ЗАПИСИ
function add_comment_on_post_update( $post_id, $post_after, $post_before ) {
if (get_option('gpt_setting_name5') == 1) {  // Действия, если чекбокс выбран
	if ( wp_is_post_revision( $post_id ) )
        return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (isset($post_before->post_status) && 'auto-draft' == $post_before->post_status) {
        return;
    }

if ('post' == $_POST['post_type']) { //проверяем, что это тип - записи
add_comment_to_post  ($post_id);
 }

} 
}
add_action( 'post_updated', 'add_comment_on_post_update', 10 , 3 );





//действие на странице списка записей
add_filter( 'bulk_actions-edit-post', 'gpt_custom_bulk_actions' );

function gpt_custom_bulk_actions( $actions ) {
    $actions['gpt_custom_action'] = __( 'GPT комментарий', 'textdomain' );
    return $actions;
}
	
//генерируем комментарий GPT к выбранным записям
add_action( 'admin_action_gpt_custom_action', 'gpt_custom_bulk_action_handler' );

function gpt_custom_bulk_action_handler() {
    $post_ids = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : array(); // получаем выбранные записи

   foreach ( $post_ids as $post_id ) {
    // генерируем комментарий GPT к каждой выбранной записи
  if (add_comment_to_post($post_id)) {
        sleep(3);
    } else {
        continue;
    }
}
    // перенаправление на страницу со списком записей
    $sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
	wp_redirect( $sendback ); 
    exit();
}

?>
