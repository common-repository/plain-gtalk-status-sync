<div class="wrap">
<h2>Gtalk Status to WordPress Usage / Settings</h2>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
<h3>Usage</h3>
<p style="margin-left:10px">Just simply put a &lt;?php get_gtalk_status();?&gt; in your theme files and you'll get a group of &lt;li&gt; tags. Don't forget to use a &lt;ol&gt; or &lt;ol&gt; to wrap it!</p>

<h3>Settings</h3>

<table class="form-table">

<tr valign="top">
<th scope="row">You GTalk Badge URL <b style="color:red">*</b></th>
<td><input style="width:98%" type="text" name="gtw_badge_url" value="<?php echo get_option('gtw_badge_url'); ?>" /><br/>
	Tips:<br/>
	<?php if (!get_option('gtw_badge_url') || get_option('gtw_badge_url')=='') { ?> 
		<div id="message" style="padding:2px 4px; width:66%;margin:10px;background-color: #c00;color:#ff8"><strong>This is your first set-up, please follow these tips carefully:</strong></div>
	<?php } ?>
	
	<ol style="list-style-type:none;font-size:11px;color:green;margin-left:10px">
	<li>1. Get your Google Talk Badge first via this <a target="_blank" href="http://www.google.com/talk/service/badge/New">link</a>. </li>
	<li>2. <span style="color:red">NEVER forget to open the "Edit" option, and check the "Show your status message"!</span></li>
	<li>3. Get the src value from that textarea. We only need the URL, not the &lt;iframe&gt; tags!</li>
	<li>4. Keep in mind that it starts with "http://", rather than the "&lt;iframe&gt;...&lt;/iframe&gt;" google gives you.</li>
	
	</ol></td>
</tr>
 
<tr valign="top">
<th scope="row">The number of messages to show</th>
<td><input type="text" name="gtw_display_number" value="<?php echo get_option('gtw_display_number'); ?>" /><br/>
	FYI: This value must be a positive integer</td>
</tr>
	
<tr valign="top">
<th scope="row">The caching frequency</th>
<td><input type="text" name="gtw_update_frequency" value="<?php echo get_option('gtw_update_frequency'); ?>" /><br/>
	FYI: Your server will cache the status information for such period, this saves a lot of your resources. This value must be a positive integer, in seconds, recommend: 1800 - half an hour</td>
</tr>

<tr valign="top">
<th scope="row">The stored data</th>
<td>
	<fieldset><legend class="hidden">Clear Current Data</legend>
	<input type="hidden" name="gtw_data" id="gtw_data" value="<?php echo htmlentities(get_option('gtw_data')); ?>" />
	<textarea style="width:97%;background:#ddd" disabled="" id="gtw_dummy" name="gtw_dummy"><?php echo htmlentities(get_option('gtw_data')); ?></textarea>
	<p>
	<label for="gtw_clear"><input id="gtw_clear" type="checkbox" value="1"/>Clear the current data:</label>
	</p>
	</fieldset>
</td>
</tr>
	
<tr valign="top"> 
<th scope="row"><label for="gtw_lang">Language</label></th> 
<td> 
<select name="gtw_lang" id="gtw_lang"> 
	<option value='en'>English</option> 
	<option value='cn'>Simplified Chinese</option>
</select> 
</td> 
</tr> 
<tr> 

<script type='text/javascript'>
/* <![CDATA[ */
		var _gtw_data =jQuery('#gtw_data').val();
		jQuery('#gtw_clear').change(function(){
			if (jQuery('#gtw_clear').attr('checked'))
			{
				jQuery('#gtw_data').val('');
				jQuery('#gtw_dummy').val('');
				
			}
			else
			{
				jQuery('#gtw_data').val(_gtw_data);
				jQuery('#gtw_dummy').val(_gtw_data);
			}
		});
/* ]]> */
</script>
</table>


<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="gtw_badge_url,gtw_display_number,gtw_data,gtw_update_frequency,gtw_lang" />

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
</p>

</form>

<h3>About</h3>
<p style="margin-left:10px">This is just a simple plugin that I'm using for my personal blog. And I am not sure whether there are really many guys loves GTalk like me. If you love this idea, please <a href="mailto:awflasher+wp.gtw@gmail.com">mail me</a>. And if there are really enough guys love this idea, I will improve this plugin in the future :)</p>
</div>