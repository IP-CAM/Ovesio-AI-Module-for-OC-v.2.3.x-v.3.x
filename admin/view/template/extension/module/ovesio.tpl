<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-module" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-module" class="form-horizontal">
        <input type="hidden" name="hash" value="<?php echo $hash; ?>"/>
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
            <li><a href="#tab-description-generator" data-toggle="tab"><?php echo $tab_description_generator; ?></a></li>
            <li><a href="#tab-metatags-generator" data-toggle="tab"><?php echo $tab_metatags; ?></a></li>
            <li><a href="#tab-translate" data-toggle="tab"><?php echo $tab_translate; ?></a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-general">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-10">
                  <select name="status" id="input-status" class="form-control">
                    <?php if ($status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-api"><?php echo $entry_api; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="api" value="<?php echo $api; ?>" placeholder="<?php echo $entry_api; ?>" id="input-api" class="form-control" />
                  <?php if (!empty($error['api'])) { ?>
                  <span class="text-danger"><?php echo $error['api']; ?></span>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-token"><span data-toggle="tooltip" title="<?php echo $entry_token_helper; ?>"><?php echo $entry_token; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="token" value="<?php echo $token; ?>" placeholder="<?php echo $entry_token; ?>" id="input-token" class="form-control" />
                  <?php if (!empty($error['token'])) { ?>
                  <span class="text-danger"><?php echo $error['token']; ?></span>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-catalog-language"><?php echo $entry_catalog_language; ?></label>
                <div class="col-sm-10">
                  <select name="catalog_language_id" id="input-catalog-language" class="form-control">
                    <?php foreach ($languages as $language) { ?>
                    <option value="<?php echo $language['language_id']; ?>" <?php echo $language['language_id'] == $catalog_language_id ? 'SELECTED' : ''; ?>><?php echo $language['name']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <fieldset class="table-responsive">
                <legend><?php echo $text_language_association; ?></legend>
                <table class="table table-bordered">
                  <thead>
                    <th><?php echo $text_system_language; ?></th>
                    <th><?php echo $text_iso2_language; ?></th>
                  </thead>
                  <tbody>
                    <?php foreach ($languages as $language) { ?>
                    <tr>
                      <td><?php echo $language['name']; ?></td>
                      <td>
                        <select name="language_match[<?php echo $language['language_id']; ?>][code]" class="form-control">
                          <option value=""></option>
                          <?php foreach ($iso2 as $_iso2) { ?>
                          <option value="<?php echo $_iso2; ?>" <?php echo $language_match[$language['language_id']]['code'] == $_iso2 ? 'SELECTED' : ''; ?>><?php echo $_iso2; ?></option>
                          <?php } ?>
                        </select>
                        <?php if(!empty($error[$language['language_id']]['code'])) { ?>
                        <span class="text-danger"><?php echo $error[$language['language_id']]['code']; ?></span>
                        <?php } ?>
                      </td>
                      <?php } ?>
                  </tbody>
                </table>
              </fieldset>

              <fieldset>
                <legend><?php echo $text_translation_callback; ?></legend>
                <div class="well well-sm">
                  <?php echo $callback; ?>
                </div>
                <div class="alert alert-info">
                  <?php echo $text_translation_callback_helper; ?>
                </div>
              </fieldset>

              <fieldset>
                <legend><?php echo $text_cronjob; ?></legend>
                <div class="well well-sm">
                  <?php echo $description_cronjob; ?>
                </div>
                <?php echo $text_cronjob_helper; ?>
              </fieldset>
            </div>
            <div class="tab-pane" id="tab-description-generator">
              <div class="alert alert-warning">
                <?php echo $text_description_generator_info; ?>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="description-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-10">
                  <select name="description_status" id="description-status" class="form-control">
                    <?php if ($description_status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <!-- <div class="form-group">
                <label class="col-sm-2 control-label" for="input-live-description"><span data-toggle="tooltip" title="<?php echo $help_live_description; ?>"><?php echo $entry_live_description; ?></span></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="live_description" value="1" <?php echo $live_description ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="live_description" value="0" <?php echo !$live_description ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div> -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_generate_product_description; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="generate_product_description" value="1" <?php echo $generate_product_description ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="generate_product_description" value="0" <?php echo !$generate_product_description ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="product-description-length"><?php echo $entry_minimum_description_length_product; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="minimum_product_descrition" id="product-description-length" class="form-control" value="<?php echo $minimum_product_descrition; ?>">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_send_stock_0; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="description_send_stock_0" value="1" <?php echo $description_send_stock_0 ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="description_send_stock_0" value="0" <?php echo !$description_send_stock_0 ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_generate_category_description; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="generate_category_description" value="1" <?php echo $generate_category_description ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="generate_category_description" value="0" <?php echo !$generate_category_description ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="category-description-length"><?php echo $entry_minimum_description_length_category; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="minimum_category_descrition" id="category-description-length" class="form-control" value="<?php echo $minimum_category_descrition; ?>">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_create_a_new_description; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="create_description_one_time_only" value="1" <?php echo $create_description_one_time_only ? 'CHECKED' : ''; ?> /> <?php echo $text_one_time_only; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="create_description_one_time_only" value="0" <?php echo !$create_description_one_time_only ? 'CHECKED' : ''; ?> /> <?php echo $text_on_each_update; ?>
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_send_disabled; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="description_send_disabled" value="1" <?php echo $description_send_disabled ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="description_send_disabled" value="0" <?php echo !$description_send_disabled ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-metatags-generator">
              <?php if($text_metatags_generator_info) { ?>
              <div class="alert alert-info">
                <?php echo $text_metatags_generator_info; ?>
              </div>
              <?php } ?>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="metatags-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-10">
                  <select name="metatags_status" id="metatags-status" class="form-control">
                    <?php if ($metatags_status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_metatags_product; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="metatags_product" value="1" <?php echo $metatags_product ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="metatags_product" value="0" <?php echo !$metatags_product ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_metatags_send_stock_0; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="metatags_send_stock_0" value="1" <?php echo $metatags_send_stock_0 ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="metatags_send_stock_0" value="0" <?php echo !$metatags_send_stock_0 ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_metatags_category; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="metatags_category" value="1" <?php echo $metatags_category ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="metatags_category" value="0" <?php echo !$metatags_category ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $text_one_time_only; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="metatags_one_time_only" value="1" <?php echo $metatags_one_time_only ? 'CHECKED' : ''; ?> /> <?php echo $text_one_time_only; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="metatags_one_time_only" value="0" <?php echo !$metatags_one_time_only ? 'CHECKED' : ''; ?> /> <?php echo $text_on_each_update; ?>
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $text_only_for_action; ?></label>
                <div class="col-sm-10">
                    <input type="hidden" name="metatags_only_for_action" value="0" />
                    <input type="checkbox" name="metatags_only_for_action" value="1" <?php echo $metatags_only_for_action && $description_status ? 'CHECKED' : ''; ?> <?php if(!$description_status) {?>disabled="disabled"<?php } ?>/> <?php echo $text_only_for_generated_descriptions; ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_metatags_send_disabled; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="metatags_send_disabled" value="1" <?php echo $metatags_send_disabled ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="metatags_send_disabled" value="0" <?php echo !$metatags_send_disabled ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-translate">
              <?php if($text_translate_after_description_generator_info) { ?>
              <div class="alert alert-info">
                <?php echo $text_translate_after_description_generator_info; ?>
              </div>
              <?php } ?>
              <div class="form-group">
                  <label class="col-sm-2 control-label" for="translation-status"><?php echo $entry_status; ?></label>
                  <div class="col-sm-10">
                    <select name="translation_status" id="translation-status" class="form-control">
                      <?php if ($translation_status) { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
              <!-- <div class="form-group">
                  <label class="col-sm-2 control-label" for="input-live-translate"><span data-toggle="tooltip" title="<?php echo $help_live_translate; ?>"><?php echo $entry_live_translate; ?></span></label>
                  <div class="col-sm-10">
                      <label class="radio-inline">
                      <input type="radio" name="live_translate" value="1" <?php echo $live_translate ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                      </label>
                      <label class="radio-inline">
                      <input type="radio" name="live_translate" value="0" <?php echo !$live_translate ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                      </label>
                  </div>
              </div> -->

              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-send-stock-0"><?php echo $entry_send_stock_0; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="send_stock_0" value="1" <?php echo $send_stock_0 ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="send_stock_0" value="0" <?php echo !$send_stock_0 ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-send-disabled"><?php echo $entry_send_disabled; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="send_disabled" value="1" <?php echo $send_disabled ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="send_disabled" value="0" <?php echo !$send_disabled ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                    </label>
                </div>
              </div>

              <!-- <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_create_a_new_translation; ?></label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                    <input type="radio" name="translate_one_time_only" value="1" <?php echo $translate_one_time_only ? 'CHECKED' : ''; ?> /> <?php echo $text_one_time_only; ?>
                    </label>
                    <label class="radio-inline">
                    <input type="radio" name="translate_one_time_only" value="0" <?php echo !$translate_one_time_only ? 'CHECKED' : ''; ?> /> <?php echo $text_on_each_update; ?>
                    </label>
                </div>
              </div> -->

            <fieldset class="table-responsive">
              <legend><?php echo $text_language_translations; ?></legend>
              <table class="table table-bordered">
                <thead>
                  <th><?php echo $text_system_language; ?></th>
                  <th><span class="fa fa-question-circle cursor-pointer" data-toggle="tooltip" title="<?php echo $text_translate_status_helper; ?>"></span> <?php echo $text_translate_status; ?></th>
                  <th><?php echo $text_translate_from; ?></th>
                </thead>
                <tbody>
                  <?php foreach ($languages as $language) { ?>
                  <tr>
                    <td><?php echo $language['name']; ?></td>
                    <td>
                      <div class="la-<?php echo $language['language_id']; ?>">
                        <label class="radio-inline">
                          <input type="radio" name="language_match[<?php echo $language['language_id']; ?>][status]" value="1" <?php echo isset($language_match[$language['language_id']]['status']) && $language_match[$language['language_id']]['status'] == 1 ? 'CHECKED' : ''; ?>> <?php echo $text_yes; ?>
                        </label>
                        <label class="radio-inline">
                          <input type="radio" name="language_match[<?php echo $language['language_id']; ?>][status]" value="0" <?php echo !isset($language_match[$language['language_id']]['status']) || $language_match[$language['language_id']]['status'] == 0 ? 'CHECKED' : ''; ?>> <?php echo $text_no; ?>
                        </label>
                      </div>
                    </td>
                    <td>
                      <div class="la-<?php echo $language['language_id']; ?>">
                        <select name="language_match[<?php echo $language['language_id']; ?>][from_language_id]" class="form-control">
                          <?php foreach ($languages as $l) { ?>
                          <option value="<?php echo $l['language_id']; ?>" <?php echo isset($language_match[$language['language_id']]['from_language_id']) && $l['language_id'] == $language_match[$language['language_id']]['from_language_id'] ? 'SELECTED' : ''; ?>><?php echo $l['name']; ?></option>
                          <?php } ?>
                        </select>
                        <?php if(!empty($error[$language['language_id']]['from_language_id'])) { ?>
                        <span class="text-danger"><?php echo $error[$language['language_id']]['from_language_id']; ?></span>
                        <?php } ?>
                      </div>
                    </td>
                    <?php } ?>
                </tbody>
              </table>
            </fieldset>

            <fieldset>
              <legend><?php echo $text_translated_fields; ?></legend>
              <strong><?php echo $text_products; ?></strong>
              <div class="well well-sm" style="height: 180px; overflow: auto;">
                <?php foreach ($product_translates as $translate) { ?>
                <div>
                  <input type="hidden" name="translate_fields[product][<?php echo $translate['key']; ?>]" value="0">
                  <input type="checkbox" name="translate_fields[product][<?php echo $translate['key']; ?>]" value="1" id="product-translate-<?php echo $translate['key']; ?>" <?php echo $translate_fields['product'][$translate['key']] ? 'CHECKED' : ''; ?>> <label for="product-translate-<?php echo $translate['key']; ?>"><?php echo $translate['label']; ?></label>
                </div>
                <?php } ?>
              </div>
              <br>
              <strong><?php echo $text_categories; ?></strong>
              <div class="well well-sm" style="height: 180px; overflow: auto;">
                <?php foreach ($category_translates as $translate) { ?>
                <div>
                  <input type="hidden" name="translate_fields[category][<?php echo $translate['key']; ?>]" value="0">
                  <input type="checkbox" name="translate_fields[category][<?php echo $translate['key']; ?>]" value="1" id="category-translate-<?php echo $translate['key']; ?>" <?php echo $translate_fields['category'][$translate['key']] ? 'CHECKED' : ''; ?>> <label for="category-translate-<?php echo $translate['key']; ?>"><?php echo $translate['label']; ?></label>
                </div>
                <?php } ?>
              </div>
              <?php echo $text_other_translations; ?><br/><br/>
            </fieldset>
            <fieldset>
              <legend><?php echo $text_translate_feeds; ?></legend>
              <div class="well well-sm">
                <?php foreach ($feeds as $feed) { ?><?php echo $feed; ?><br><?php } ?>
              </div>
            </fieldset>
          </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  $(document).ready(function() {
    $('#input-catalog-language').on('change', function() {
      $('[class^="la-"]').show();
      var language_id = $(this).val();
      $('[class="la-' + language_id + '"]').hide();
    }).trigger('change');
  });
</script>
<?php echo $footer; ?>