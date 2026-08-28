<?php
/**
 * Quote composer for a wall publication.
 */
?>
<div class="ossn-wall-quote-form-content">
		<p><?php echo ossn_print('repost:quote:hint'); ?></p>
		<textarea id="ossn-wall-quote-text" name="quote" rows="5" maxlength="5000" required autofocus></textarea>
		<input type="hidden" name="post" value="<?php echo (int) $params['post']->guid; ?>" />
		<input type="submit" class="hidden" id="ossn-wall-quote-save" />
</div>
<script>Ossn.WallQuoteForm();</script>
