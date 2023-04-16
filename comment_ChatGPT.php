<?php
/*
Plugin Name: WP Комментатор ChatGPT
Plugin URI: https://sochka.com
Description: Искусственный интеллект (ChatGPT) оставляет осмысленный комментарий к вашей записи (каждый раз: при создания новой, при редактировании старой). Дополняет новость уникальным контентом! Стимулирует дальнейшую дискуссию читателями! Настройте: НАСТРОЙКИ - ОБСУЖДЕНИЯ...
Version: 0.1
Author: Yaroslav Sochka
Author URI: https://sochka.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

//настройки плагина

add_action( 'admin_init', 'gpt_settings_api_init' );

function gpt_settings_api_init() {

	// блок опций на базовую страницу "Обсуждение"
	add_settings_section(
		'gpt_setting_section', // секция
		'Настройки комментатора GPT',
		'gpt_setting_section_callback_function',
		'discussion' // страница
	);

	//поля опций. Указываем название, описание,

	add_settings_field(
		'gpt_setting_name',
		'API ключ GPT',
		'gpt_setting_callback_function',
		'discussion', // страница
		'gpt_setting_section' // секция
	);
	add_settings_field(
		'gpt_setting_name2',
		'Email от имени которого будут публиковаться комментарии',
		'gpt_setting_callback_function2',
		'discussion', // страница
		'gpt_setting_section' // секция
	);
		add_settings_field(
		'gpt_setting_name3',
		'Имя комментатора',
		'gpt_setting_callback_function3',
		'discussion', // страница
		'gpt_setting_section' // секция
	);

	// Регистрируем опции, чтобы они сохранялись при отправке
	register_setting( 'discussion', 'gpt_setting_name' );
	register_setting( 'discussion', 'gpt_setting_name2' );
	register_setting( 'discussion', 'gpt_setting_name3' );
}


function gpt_setting_section_callback_function() {
	echo '<p>Внимательно заполните ВСЕ поля для корректной работы комментатора ChatGPT</p>';
}


function gpt_setting_callback_function() {
	?>
	<input
		name="gpt_setting_name"
		type="text"
		value="<?= esc_attr( get_option(  'gpt_setting_name' ) ) ?>"
		class="code"
	/>  <a href="https://platform.openai.com/account/api-keys" target="_blank" title="Требуется регистрация">Получить API</a>
	<?php
}

function gpt_setting_callback_function2() {
	?>
	<input
		name="gpt_setting_name2"
		type="text"
		value="<?= esc_attr( get_option( 'gpt_setting_name2' ) ) ?>"
		class="code2"
	 /> (оптимально, если у него есть Gravatar)
	<?php
}

function gpt_setting_callback_function3() {
	?>
	<input
		name="gpt_setting_name3"
		type="text"
		value="<?= esc_attr( get_option( 'gpt_setting_name3' ) ) ?>"
		class="code3"
	 /> Например: newsBOT
	<?php
}


//обращаемся к ИИ за комментарием
function add_comment_on_post_update( $post_id, $post_after, $post_before ) {

    // If this is just a revision, don't send the email.
    if ( wp_is_post_revision( $post_id ) )
        return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (isset($post_before->post_status) && 'auto-draft' == $post_before->post_status) {
        return;
    }

    if ('post' == $_POST['post_type']) {

$inputr = array("Прокомментируй новость: ", "Что ты думаешь по этому поводу: ", "Как ты к этому относишься: ", "Хорошо это или плохо: ", "Есть ли в этом смысл: ");
$fraza = $inputr[array_rand($inputr)];
		
$api = esc_attr( get_option(  'gpt_setting_name' ) );
$titlebot = $fraza.' '.get_the_title( $post_id );

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer '.$api.''
];

$data = [
    'model' => 'text-davinci-003',
    'prompt' => $titlebot,
    'max_tokens' => 1024,
    'temperature' => 0.8
];
$ch = curl_init("https://api.openai.com/v1/completions");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = json_decode(curl_exec($ch), true);
		
if($e = curl_error($ch)) {
$commentbot = '';
} else {
$commentbot = $response['choices'][0]['text'];	
}
curl_close($ch);


        $data = array(
            'comment_post_ID' => $post_id,
            'comment_author' => esc_attr( get_option(  'gpt_setting_name3' ) ),
            'comment_author_email' => esc_attr( get_option(  'gpt_setting_name2' ) ),
            'comment_author_url' => '',
            'comment_content' => $commentbot,
	'comment_parent'       => 0,
	'user_id'              => '',
	'comment_author_IP'    => '127.0.0.1',
	'comment_agent'        => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
	'comment_date' => date('Y-m-d H:i:s'),
	'comment_approved'     => 1
        );
	
		if (strlen($commentbot) > 25) {wp_insert_comment($data);}


    }

}
add_action( 'post_updated', 'add_comment_on_post_update', 10 , 3 );

?>
