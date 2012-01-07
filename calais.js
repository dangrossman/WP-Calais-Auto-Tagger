<script type="text/javascript">
//<![CDATA[

    var currentTags = new Array();

    jQuery(document).ready(function() {

        //Populate the tag lists
        calais_redisplay_tags();
        calais_gettags();

        jQuery('#calais_suggest_button').click(function() {
            calais_gettags();
        });

        jQuery('#calais_add_button').click(function() {
            jQuery.each(jQuery('#calais_manual').val().split(','), function(index, val) {
                calais_add_tag(jQuery.trim(val));
            });
            jQuery('#calais_manual').val();
        });

        jQuery('#tagsdiv-post_tag').hide();

    });

    // Performs AJAX request to retrieve tag suggestions 
    function calais_gettags() {

        jQuery('#calais_suggestions').html('Getting suggestions...');

        var content = '';
        if (typeof tinyMCE != 'undefined' && tinyMCE.activeEditor != null && tinyMCE.activeEditor.isHidden() == false) {
            content = tinyMCE.activeEditor.getContent();
        }
        content = jQuery('#content').val();

        jQuery.post(ajaxurl, {text: content, action: 'calais_gettags', cookie: document.cookie}, calais_showtags);

    }

    // Displays suggested tags in the "tag suggestions" area
    function calais_showtags(tags) {

        jQuery('#calais_suggestions').html('');

        if (tags.length == 0) return;

        jQuery.each(tags.split(', '), function(index, val) {

            jQuery('#calais_suggestions').append(jQuery('<div></div>')
                .addClass('calais_tag')
                .append(jQuery('<img>')
                    .attr({src: '<?php get_bloginfo('wpurl'); ?>/wp-content/plugins/calais-auto-tagger/images/add.png'})
                )
                .append(jQuery('<span>')
                    .html(val)
                )
                .click(function() {
                    calais_add_tag(jQuery(this).find('span').html());
                    jQuery(this).hide();
                })
            );

        });

    }

    // Add a tag to the post
    function calais_add_tag(tag) {

        if (jQuery.inArray(tag, currentTags) == -1) {
            var list = jQuery('#calais_taglist').val();
            if (list.length > 0) {
                jQuery('#calais_taglist').val(list + ', ' + tag);
            } else {
                jQuery('#calais_taglist').val(tag);
            }
        }

        calais_redisplay_tags();

    }

    // Remove a tag from the post
    function calais_remove_tag(tag) {

        jQuery.each(currentTags, function(index, val) {
            if (val == tag) {
                currentTags.splice(index, 1);
            }
        });

        jQuery('#calais_taglist').val(currentTags.join(', '));

        calais_redisplay_tags();

    }

    // Update the clickable tag area
    function calais_redisplay_tags() {

        jQuery('#calais_tag_box').html('');

        currentTags = jQuery('#calais_taglist').val().split(', ');

        jQuery.each(currentTags, function(index, val) {

            if (val == '') return;

            jQuery('#calais_tag_box').append(jQuery('<div></div>')
                .addClass('calais_tag')
                .append(jQuery('<img>')
                    .attr({src: '<?php get_bloginfo('wpurl'); ?>/wp-content/plugins/calais-auto-tagger/images/delete.png'})
                )
                .append(jQuery('<span>')
                    .html(val)
                )
                .click(function() {
                    calais_remove_tag(jQuery(this).find('span').html());
                    jQuery(this).hide();
                })
            );

        });

    }

//]]>
</script>
