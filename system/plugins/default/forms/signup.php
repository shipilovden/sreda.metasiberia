<?php
/**
 * Login Form
 */
?>
<div class="custom-row">
    <div class="custom-col">
        <input type="text" name="firstname" placeholder="<?php echo ossn_print('first:name'); ?>"/>
    </div>
    <div class="custom-col">
        <input type="text" name="lastname" placeholder="<?php echo ossn_print('last:name'); ?>"/>
    </div>
</div>

<div class="custom-row">
    <div class="custom-col">
        <input type="text" name="email" placeholder="<?php echo ossn_print('email'); ?>"/>
    </div>
    <div class="custom-col">
        <input name="email_re" type="text" placeholder="<?php echo ossn_print('email:again'); ?>"/>
    </div>
</div>
<div class="form-group-modern">
    <input type="text" name="username" maxlength="50" placeholder="<?php echo ossn_print('username'); ?>"/>
</div>

<div class="form-group-modern">
    <input type="password" name="password" placeholder="<?php echo ossn_print('password'); ?>" />
</div>

<?php
$signup_fields = ossn_default_user_fields();
$signup_birthdate_fields = array();
$signup_gender_fields = array();
$signup_other_fields = array();

/* Keep the built-in registration order explicit: birthdate, language, gender. */
if($signup_fields){
    foreach($signup_fields as $section => $section_fields){
        foreach($section_fields as $field_type => $field_items){
            foreach($field_items as $field_item){
                $field_name = isset($field_item['name']) ? $field_item['name'] : '';
                if($field_name === 'birthdate'){
                    $signup_birthdate_fields[$section][$field_type][] = $field_item;
                } elseif($field_name === 'gender'){
                    $signup_gender_fields[$section][$field_type][] = $field_item;
                } else {
                    $signup_other_fields[$section][$field_type][] = $field_item;
                }
            }
        }
    }
}

if($signup_birthdate_fields){
    echo ossn_plugin_view('user/fields/item', array('items' => $signup_birthdate_fields));
}
?>

<?php
$signup_languages = ossn_get_installed_translations(false);
if($signup_languages){
?>
<div class="sreda-signup-language">
    <?php
    echo ossn_plugin_view('input/dropdown', array(
        'name' => 'language',
        'id' => 'sreda-signup-language',
        'aria-label' => ossn_print('language'),
        'value' => ossn_site_settings('language'),
        'options' => $signup_languages,
    ));
    ?>
</div>
<?php } ?>

<?php
if($signup_gender_fields){
    echo ossn_plugin_view('user/fields/item', array('items' => $signup_gender_fields));
}
if($signup_other_fields){
    echo ossn_plugin_view('user/fields/item', array('items' => $signup_other_fields));
}
?>

<div>
<?php echo ossn_fetch_extend_views('forms/signup/before/submit'); ?>
</div>
<div id="ossn-signup-errors" class="alert alert-danger d-none"></div>

<p class="terms-text">
    <?php echo ossn_print('account:create:notice'); ?>
    <a target="_blank" rel="noopener noreferrer" href="<?php echo ossn_site_url('site/terms'); ?>"><?php echo ossn_print('site:terms'); ?></a>
    <?php echo ossn_print('account:create:notice:and'); ?>
    <a target="_blank" rel="noopener noreferrer" href="<?php echo ossn_site_url('site/privacy'); ?>"><?php echo ossn_print('account:create:privacy'); ?></a>.
</p>

<div class="ossn-loading ossn-hidden"></div>
<input type="submit" id="ossn-submit-button" class="btn btn-primary" value="<?php echo ossn_print('create:account'); ?>" />

<script>
(function() {
    var signupLanguage = document.getElementById('sreda-signup-language');
    if (!signupLanguage || signupLanguage.dataset.sredaLanguageHandler) {
        return;
    }
    signupLanguage.dataset.sredaLanguageHandler = '1';
    signupLanguage.addEventListener('change', function() {
        var currentUrl = new window.URL(window.location.href);
        currentUrl.searchParams.set('language', this.value);
        window.location.assign(currentUrl.toString());
    });
}());
</script>
