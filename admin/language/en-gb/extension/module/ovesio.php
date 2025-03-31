<?php
// Heading
$_['heading_title']    = 'Ovesio [<a target="_blank" href="https://ovesio.com">ovesio.com</a>]';

// Text
$_['text_extension']   = 'Extensions';
$_['text_success']     = 'Success: You have modified Ovesio AI module!';
$_['text_edit']        = 'Edit Ovesio AI Module';
$_['text_system_language']        = 'System Language';
$_['text_iso2_language']        = 'ISO2 Language';
$_['text_translate_status'] = 'Translate';
$_['text_translate_status_helper'] = 'Will be automatically ignored if is Catalog Language';
$_['text_translate_from'] = 'Translate From';
$_['text_language_association'] = 'Language Association';
$_['text_language_translations'] = 'Language Translation';
$_['text_translated_fields'] = 'Translated Fields';
$_['text_products'] = 'Products';
$_['text_categories'] = 'Categories';

$_['text_name'] = 'Name';
$_['text_description'] = 'Description';
$_['text_tag'] = 'Tags';
$_['text_meta_title'] = 'Meta Title';
$_['text_meta_description'] = 'Meta Description';
$_['text_meta_keyword'] = 'Meta Keywords';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_yes'] = 'Yes';
$_['text_no'] = 'No';
$_['text_translate_feeds'] = 'Translate feeds';
$_['text_translation_callback'] = 'Callback url';
$_['text_translation_callback_helper'] = '* This is the URL where translations/descriptions will be send by Ovesio.com. The URL must be reachable from outside.';
$_['text_cronjob'] = 'Cronjob';
$_['text_cronjob_helper'] = '* Each time the cron runs, it will process only 40 resources at a time.';
$_['text_description_generator_info'] = 'Changing the options in this section may affect translations already made with Ovesio. If you re-generate descriptions for products or categories that have already been translated, they will need to be translated again.';
$_['text_translate_after_description_generator_info'] = 'Since you have enabled automatic description and/or metatags generator, resource translation will occur after the description is generated, according to the settings you chose.';
$_['text_metatags_generator_info'] = 'Since you have enabled automatic description generation, metatags generator will occur after the description is generated';
$_['text_one_time_only'] = 'One time only';
$_['text_on_each_update'] = 'On each resource update';
$_['text_other_translations'] = '* product options, product attributes and product attribute groups will be translated as well.';

$_['tab_general'] = 'General';
$_['tab_description_generator'] = 'AI Description Generator';
$_['tab_translate'] = 'Translate Settings';
$_['tab_metatags'] = 'AI SEO MetaTags generator';

// Entry
$_['entry_status']     = 'Status';
$_['entry_token']     = 'API Token';
$_['entry_api']     = 'API Url';
$_['entry_token_helper']     = 'API Token is found in Ovesio.com platform, in Settings menu';
$_['entry_catalog_language']     = 'Catalog Language';
// $_['entry_live_translate']     = 'Live translate';
// $_['help_live_translate']     = 'Ensure that you request a new translation each time a resource is edited. This approach keeps your content up-to-date across all languages. If this feature is disabled, you can still translate your content by setting up a translation feed on Ovesio.com. The feed URLs are listed under "Translate feeds."';
// $_['entry_live_description']     = 'Live description';
// $_['help_live_description']     = 'Ensure that you request a new description creating each time a resource is edited. This approach keeps your content up-to-date across all languages. If this feature is disabled, you can still auto generate descriptions by setting up a cron job(check cronjob section)';
$_['entry_send_stock_0'] = 'Include products out of stock(quantity <= 0)';
$_['entry_send_disabled'] = 'Include disabled products and categories';
$_['entry_generate_product_description'] = 'Generate description for products';
$_['entry_generate_category_description'] = 'Generate description for categories';
$_['entry_minimum_description_length_product'] = 'Ignore product descriptions larger than X characters';
$_['entry_minimum_description_length_category'] = 'Ignore category descriptions larger than X characters';
$_['entry_create_a_new_description'] = 'Create a new description';
$_['entry_create_a_new_translation'] = 'Create a new translation';
$_['entry_metatags_product'] = 'Generate MetaTags for products';
$_['entry_metatags_send_stock_0'] = 'Include products out of stock(quantity <= 0)';
$_['entry_metatags_send_disabled'] = 'Include disabled products and categories';
$_['entry_metatags_category'] = 'Generate MetaTags for categories';

// Buttons
$_['button_cancel'] = 'Cancel';
$_['button_save'] = 'Save';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify Ovesio AI module!';
$_['error_code'] = 'Language association is required';
$_['error_token'] = 'A valid token is required';
$_['error_from_language_id'] = 'You cannot translate from the same language';
$_['error_from_language_id1'] = 'Selected language to translate from is disabled. This must be either activated or the catalog language';
$_['error_warning'] = 'Warning: Please check the form carefully for errors!';