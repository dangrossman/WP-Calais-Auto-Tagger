<?php
/***************************************************************************

Plugin Name: Calais Auto Tagger
Plugin URI: http://www.dangrossman.info/wp-calais-auto-tagger
Description: Suggests tags for your posts based on semantic analysis of your post content with the Open Calais API.
Version: 1.4
Author: Dan Grossman
Author URI: http://www.dangrossman.info

***************************************************************************/

// Include the Open Calais Tags class by Dan Grossman
// http://www.dangrossman.info/open-calais-tags

require('opencalais.php');

// WordPress hooks

add_action('admin_menu', 'calais_init');
add_action('save_post', 'calais_savetags', 10, 2);
add_action('wp_ajax_calais_gettags', 'calais_gettags');

// Adds the tag suggestion box to the post page, and the configuration link to the settings menu

function calais_init() {
	$post_types = apply_filters( 'calais_post_types', array( 'post' ) );
	foreach( $post_types as $type ) {
		add_meta_box('calais', 'Calais Auto Tagger', 'calais_box', $type, 'normal', 'high');
	}
	add_submenu_page('options-general.php', 'Calais API Key', 'Calais API Key', 10, __FILE__, 'calais_conf');
}

// Renders the tag suggestion box

function calais_box() {

    include('calais.css');
    include('calais.js');

	global $post;
	$existing_tags = wp_get_post_tags($post->ID);
	
	$tags = array();
	
	if (count($existing_tags) > 0) {
	    foreach ($existing_tags as $tag) {
	        if ($tag->taxonomy == 'post_tag') {
	            $tags[] = $tag->name;
            }
	    }
	}

	echo '<input type="hidden" name="calais_taglist" id="calais_taglist" value="' . implode(', ', $tags) . '">';
    include('calaisbox.html');
	
}

// Called by WordPress when a post is saved
// Sets the post tags based on the hidden input field our tagging interface populated

function calais_savetags($post_id, $post) {

	if ($post->post_type == 'revision')
		return;

	if (!isset($_POST['calais_taglist'])) 
		return;

	$taglist = $_POST['calais_taglist'];
	$tags = split(', ', $taglist);
	if (strlen(trim($taglist)) > 0 && count($tags) > 0) {
		wp_set_post_tags($post_id, $tags);
	} else {
		wp_set_post_tags($post_id, array());
	}
	
}

// Callback function for the AJAX request for tag suggestions
// Returns a list of tags separated by a comma and a space

function calais_gettags() {

    if (empty($_POST['text']))
        die("");
	
	$content = stripslashes($_POST['text']);

	$key = get_option('calais-api-key');
	if (empty($key)) {
		die("You have not yet configured this plugin. You must add your Calais API key in the settings menu.");
	}
	
	$oc = new OpenCalais($key);
	$entities = $oc->getEntities($content);
	
	if (count($entities) == 0)
		die("");

    $tags = array();
    foreach ($entities as $type => $array) 
        foreach ($array as $tag)
            $tags[] = $tag;

	die(implode($tags, ', '));
	
}

// Called by WordPress to display the configuration page

function calais_conf() {

    if (isset($_POST['calais-api-key'])) {
        update_option('calais-api-key', $_POST['calais-api-key']);
        echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div>';
    }

    ?>
    
    <div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2>Calais Configuration</h2>
    
    <form action="" method="post" id="calais-conf">

    <p>The Calais Auto Tagger plugin requires an API key. If you don't have one, <a href="http://www.opencalais.com/" target="_blank">visit the Open Calais site</a>, and click the "Request API Key" link at the top of the page.</p>

    <p>
        <label for="calais-api-key">What is your Open Calais API Key?</label><br />
        <input type="text" name="calais-api-key" value="<?php echo get_option('calais-api-key'); ?>" />
    </p>

    <p class="submit">
        <input type="submit" value="Submit" />
    </p>

    </form>
    </div>

    <?php
}

